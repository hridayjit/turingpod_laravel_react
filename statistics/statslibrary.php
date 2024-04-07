<?php
$v=9;
$alpha=0.05;
//-----------------------Z critical----------------------------------------------

function zcdf($z){
    $k=50;
    $iter=0;
    for ($n=0; $n<$k+1; $n++){
        $iter=$iter+((pow(-1, $n)*pow($z, (2*$n+1)))/(pow(2, $n)*gamma($n+1)*(2*$n+1)));
    }
    return (0.5+(1/sqrt(2*pi()))*$iter);
}

function probit($p){
    $a=array(-39.69683028665376, 220.9460984245205, -275.9285104469687, 138.3577518672690, -30.66479806614716, 2.506628277459239);
    $b=array(-54.47609879822406, 161.5858368580409, -155.6989798598866, 66.80131188771972, -13.28068155288572);
    $c=array(-0.007784894002430293, -0.3223964580411365, -2.400758277161838, -2.549732539343734, 4.374664141464968, 2.938163982698783);
    $d=array(0.007784695709041462, 0.3224671290700398, 2.445134137142996, 3.754408661907416);
    if ($p>0 && $p<0.02425){
        $q=sqrt(-2*log($p));
        $x=((((($c[0]*$q+$c[1])*$q+$c[2])*$q+$c[3])*$q+$c[4])*$q+$c[5])/(((($d[0]*$q+$d[1])*$q+$d[2])*$q+$d[3])*$q+1);
    }
    else if($p>=0.02425 && $p<=(1-0.02425)){
        $q=$p-0.5;
        $r=$q*$q;
        $x=((((($a[0]*$r+$a[1])*$r+$a[2])*$r+$a[3])*$r+$a[4])*$r+$a[5])*$q /
        ((((($b[0]*$r+$b[1])*$r+$b[2])*$r+$b[3])*$r+$b[4])*$r+1);
    }
    else if ($p<1 && $p>1-0.02425){
        $q=sqrt(-2*log(1-$p));
        $x=-((((($c[0]*$q+$c[1])*$q+$c[2])*$q+$c[3])*$q+$c[4])*$q+$c[5]) /
        (((($d[0]*$q+$d[1])*$q+$d[2])*$q+$d[3])*$q+1);
    }
    return $x;
}
//----------------Student t critical-----------------------------------
function tValue($alpha, $v){
    if($v!=1){
        $g=($v-1.5-(0.1/$v)+(0.5825/($v*$v)))/(($v-1)*($v-1));
        $z=probit($alpha);
        return sqrt($v*exp($z*$z*$g)-$v);
    }
    else{
        return 6.314;
    }
}
//------------------------Gamma distribution-------------------------
function gamma($z){
    if ($z==1){
        return 1;
    }
    elseif ($z<1){
        $y=0.5772156649;
        $alpha=0.35;
        $g1=$alpha*((1/$z)-$y+(($z*(6*$y*$y+pi()*pi()))/12));
        $g2=(12*$y+(pi()*pi()-6*$y*$y)*$z)/(12*$y+(pi()*pi()+6*$y*$y)*$z);
        $g3=(1/$z)*$g2*(1-$alpha);
        return $g1+$g3;

    }
    elseif($z>1){
        $x=$z-1;
        return sqrt(2*pi()*$x)*(pow($x, $x)/exp($x))*(pow($x*sinh(1/$x), $x/2)*exp(7/(324*$x*$x*$x*(35*$x*$x+33))));
    }
    
}

//-------------------chi-square critical------------------------------------
function initialW($n, $f, $x){
    $alph=$f/2;
    $y=$x/2;
    if ($x<$f){
        $T=array();
        $T[$n]=($y*(1-$n-$alph))/(($alph+2*$n-1+$n*$y)/($alph+2*$n));
        for($k=$n-1; $k>1; $k--){
            $T[$k]=($y*(1-$k-$alph))/(($alph+2*$k-1+$k*$y)/($alph+2*$k+$T[$k+1]));
        }
        $cfl=1-$y/($alph+1+$y/($alph+2+$T[2]));
        $iyalph=$alph*log($y)-$y-log(gamma($alph+1))-log($cfl);
        return exp($iyalph);
    }
    else if ($x>=$f){
        $T=array();
        $T[$n]=($n-$alph)/($y+$n);
        for($k=$n-1; $k>1; $k--){
            $T[$k]=($k-$alph)/($y+$k/(1+$T[$k+1]));
        }
        $cfu=1+(1-$alph)/($y+1/(1+$T[2]));
        $iyalph=($alph-1)*log($y)-$y-log(gamma($alph))-log($cfu);
        return 1-exp($iyalph);
    }
}

function pchisq($chi, $f, $ncp){
    $N=50;
    $lam=$ncp/2;
    $P=array();
    $W=array();
    $S=array();
    $P[0]=exp(-$lam);
    $W[0]=initialW(25, $f, $chi);
    $lnSin=(($f-2)/2)*log($chi)-($chi/2)-(($f-2)/2)*log(2)-log(gamma($f/2));
    $S[0]=exp($lnSin);
    $sum=$P[0]*$W[0];
    for ($i=1; $i<$N+1; $i++){
        $P[$i]=($lam/$i)*$P[$i-1];
        $S[$i]=($chi/($f+2*$i-2))*$S[$i-1];
        $W[$i]=-$S[$i]+$W[$i-1];
        $sum=$sum+$P[$i]*$W[$i];
    }
    return 1-$sum;
}



function qchisq($p, $f){
    $z=probit(1-$p);
    if ($p>=0.05){
        if ($f>=10){
            $result= $f+(2*($z*$z-1))/3+$z*sqrt(2*$f-1);
        }
        elseif($f>=3 && $f<10){
            $result= $f*pow((1-(2/(9*$f))+$z*sqrt(2/(9*$f))), 3);
        }
        elseif($f==2){
            $result= -2*log($p);
        }
        elseif($f==1){
            $z1=probit(($p)/2);
            $result= $z1*$z1;
        }
    }
    elseif($p<0.05){
        if ($f>=3){
            $result= $f*pow((1-(2/(9*$f))+$z*sqrt(2/(9*$f))), 3); 
        }
        elseif ($f==2){
            $result= -2*log($p);
        }
        elseif ($f==1){
            $z1=probit(($p)/2);
            $result= $z1*$z1;
        }
    }
    return $result;
}
//----------F critical--------------------------

function fValue($p, $v1, $v2){
    $z=probit(1-$p);
    $err=0.001;
    $x=1;
    $c=pow(($z*sqrt(2/(9*$v1))+1-(2/(9*$v1))), 3);
    $a=6*$v2+3*$v1-6;
    $b=6*$v2;
    $diff=1;
    
    while($diff>$err){
        $x1=$x;
        $fx=((($v1*$x+$a)*$x)/(4*$v1*$x+$b))-$c;
        $fxd=(4*$v1*$v1*$x*$x+2*$b*$v1*$x+$a*$b)/pow(4*$v1*$x+$b, 2);
        $x=$x-($fx/$fxd);
        $diff=$x-$x1;
        if ($diff<0){
            $diff=-1*$diff;
        }
        elseif($diff>=0){
            $diff=1*$diff;
        }
    }
    return $x;
}

function fcdf($f, $v1, $v2){
    $n1=(2*$v2+(($v1*$f)/3)+$v1-2)*$f;
    $n2=(2*$v2)+((4*$v1*$f)/3);
    $z=(pow($n1/$n2, 1/3)-(1-(2/(9*$v1))))/pow((2/(9*$v1)), 0.5);
    return zcdf($z);
}

//-------------Matrix operations------------------------------------
function identity_matrix($n)
{
	$I = array();
	for ($i = 0; $i < $n; ++ $i) {
		for ($j = 0; $j < $n; ++ $j) {
			$I[$i][$j] = ($i == $j) ? 1 : 0;
		}
	}
	return $I;
}

function invert($A){
	$n = count($A);
	$I = identity_matrix($n);
	for ($i = 0; $i < $n; ++ $i) {
		$A[$i] = array_merge($A[$i], $I[$i]);
	}

	for ($j = 0; $j < $n-1; ++ $j) {
		for ($i = $j+1; $i < $n; ++ $i) {
			if ($A[$i][$j]!== 0) {
				$scalar = $A[$j][$j] / $A[$i][$j];
				for ($jj = $j; $jj < $n*2; ++ $jj) {
					$A[$i][$jj] *= $scalar;
					$A[$i][$jj] -= $A[$j][$jj];
				}
			}
		}
	}
	for ($j = $n-1; $j > 0; -- $j) {
		for ($i = $j-1; $i >= 0; -- $i) {
			if ($A[$i][$j] !== 0) {
				$scalar = $A[$j][$j] / $A[$i][$j];
				for ($jj = $i; $jj < $n*2; ++ $jj) {
					$A[$i][$jj] *= $scalar;
					$A[$i][$jj] -= $A[$j][$jj];
				}
			}
		}
	}
	for ($j = 0; $j < $n; ++ $j) {
		if ($A[$j][$j] !== 1) {
			$scalar = 1 / $A[$j][$j];
			for ($jj = $j; $jj < $n*2; ++ $jj) {
				$A[$j][$jj] *= $scalar;
			}
		}
	}
	$Inv = array();
	for ($i = 0; $i < $n; ++ $i) {
		$Inv[$i] = array_slice($A[$i], $n);
	}
	return $Inv;
}

function transpose($A){
    $nRows=count($A);
    $At=array(array());
    if (is_array($A[0])){
        $nCols=count($A[0]);
        for($i=0;$i<$nRows;$i++){
            for($j=0;$j<$nCols;$j++){
                $At[$j][$i]=$A[$i][$j];
            }      
        }
    }
    else{
        for($i=0;$i<$nRows;$i++){
            for($j=0;$j<1;$j++){
                $At[$j][$i]=$A[$i];
            }
        }
    }
    return $At;
}

function matrixMult($A, $B){
    $countBj=count($B);
    $countAi=count($A);
    $countAj=count($A[0]);

    if ($countBj==$countAj){
       if(is_array($B[0])){
            $product=array(array());
            $countBk=count($B[0]);
            for($i=0;$i<$countAi;$i++){
                for($k=0;$k<$countBk;$k++){
                    $prod=0;
                    for($j=0;$j<$countAj;$j++){
                        $prod=$prod+$A[$i][$j]*$B[$j][$k];
                    }
                    $product[$i][$k]=$prod;
                }
            }
            return $product;
       }
       else{
            $product=array();
            for($i=0;$i<$countAi;$i++){
                $prod=0;
                for($j=0;$j<$countAj;$j++){
                    $prod=$prod+$A[$i][$j]*$B[$j];
                }
                $product[$i]=$prod;
            }
            return $product;
        }
    }
}

//------------------------------Polynomial interpolation--------------------------
function intConst($x, $y){
    $lenX=count($x);
    $deg=$lenX-1;
    $xMatrix=array(array());
    for($i=0; $i<$lenX; $i++){
        for ($j=0; $j<=$deg; $j++){
            $xMatrix[$i][$j]=pow($x[$i], $j);
        }
    }
    $xTranspose=transpose($xMatrix);
    $xMatSq=matrixMult($xTranspose, $xMatrix);
    $xy=matrixMult($xTranspose, $y);
    $xInv=invert($xMatSq);
    return matrixMult($xInv, $xy);
}

function polyInterpol($x1, $x, $y){
    $lenX=count($x);
    $deg=$lenX-1;
    $b=intConst($x, $y);
    $sum=0;
    for ($i=0; $i<$deg+1; $i++){
        $sum=$sum+$b[$i]*pow($x1, $i);
    }
    return $sum;
}
//----------------------------------Polynomial Regression--------------------------------------------------
function xArray($deg, $x){
    $n=count($x);
    $xsum=array();
    for ($i=0; $i<(2*$deg)+1; $i++){
        $sum=0;
        for($j=0; $j<$n; $j++){
            $sum=$sum+pow($x[$j], $i);
        }
        $xsum[$i]=$sum;
    }
    $xArr=array(array());
    for ($k=0; $k<$deg+1; $k++){
        for($l=0; $l<$deg+1; $l++){
            $xArr[$k][$l]=$xsum[$l+$k];
        }
    }
    return $xArr;
}

function yArray($deg, $x, $y){
    $n=count($y);
    $yArr=array();
    for ($i=0; $i<$deg+1; $i++){
        $sum=0;
        for($j=0; $j<$n; $j++){
            $sum=$sum+$y[$j]*pow($x[$j], $i);
        }
        $yArr[$i]=$sum;
    }
    return $yArr;
}

function coffBestFit($deg, $x, $y){
    $X=xArray($deg, $x);
    $Y=yArray($deg, $x, $y);
    $Xinv=invert($X);
    return matrixMult($Xinv, $Y);
}

//r value
function rVal ($x, $y){
    $n=count($x);
    $xstdv=stdev($x);
    $ystdv=stdev($y);
    $sumx=0;
    for($i=0; $i<$n; $i++){
        $sumx=$sumx+$x[$i];
    }
    $xavg=$sumx/$n;
    $sumy=0;
    for($i=0; $i<$n; $i++){
        $sumy=$sumy+$y[$i];
    }
    $yavg=$sumy/$n;
    $sumcov=0;
    for($i=0; $i<$n; $i++){
        $sumcov=$sumcov+($x[$i]-$xavg)*($y[$i]-$yavg);
    }
    $cov=$sumcov/($n-1);
    return $cov/($xstdv*$ystdv);
}

function regMeasures($deg, $x, $y){
    $ysum=0;
    $n=count($x);
    for($l=0; $l<count($y); $l++){
        $ysum=$ysum+$y[$l];
    }
    $yavg=$ysum/count($y);
    $b=coffBestFit($deg, $x, $y);
    $sr=0;
    for($i=0; $i<count($x); $i++){
        $sum=0;
        for($j=0; $j<count($b); $j++){
            $sum=$sum+$b[$j]*pow($x[$i], $j);
        }
        $sr=$sr+pow($y[$i]-$sum, 2);
    }
    $sy=0;
    for($k=0; $k<count($y); $k++){
        $sy=$sy+pow($y[$k]-$yavg, 2);
    }
    $rsq=($sy-$sr)/$sy;
    $stdev=sqrt($sr/($n-2));
    //$rsq=pow(rVal($x, $y), 2);
    return array($stdev, $rsq);
}

function polyReg($x1, $deg, $x, $y){
    if ($deg<count($x)-1){
        $b=coffBestFit($deg, $x, $y);
        $sum=0;
        for($i=0; $i<$deg+1; $i++){
            $sum=$sum+$b[$i]*pow($x1, $i);
        }
        return $sum;
    }
}

//--------------------------------exponential regression----------------------
function expReg($x1, $x, $y){
    $ynew=array();
    for($i=0; $i<count($y); $i++){
        $ynew[$i]=log($y[$i]);
    }
    $yprocessed=polyReg($x1, 1, $x, $ynew);
    return exp($yprocessed);
}
//---------------------------------logarithmic regression-------------------
function logReg($x1, $x, $y){
    $xnew=array();
    for ($i=0; $i<count($x); $i++){
        $xnew[$i]=log($x[$i]);
    }
    return polyReg($x1, 1, $xnew, $y);
}
//--------------------------------Ranking Algorithm------------------------
//Pass array $a
function convertToRank($a){
    $rank=array();
    $n=count($a);
    for ($i=0; $i<$n; $i++){
        $r=1;
        $s=1;
        for ($j=0; $j<$n; $j++){
            if ($a[$j]<$a[$i] && $i!=$j){
                $r+=1;
            }
            else if($a[$j]==$a[$i] && $i!=$j){
                $s+=1;
            }
        }
        $rank[$i]=$r+($s-1)/2;
    }
    return $rank;
}
//-------------------------summation---------
function sum($array){
    $n=count($array);
    $sum=0;
    for($i=0; $i<$n; $i++){
        $sum+=$array[$i];
    }
    return $sum;
}

function sorting($rank){
    for ($i=0; $i<count($rank); $i++){
        for($j=$i+1; $j<count($rank); $j++){
            if($rank[$i]>$rank[$j]){
                $c=$rank[$j];
                $c1=$rank[$i];
                $rank[$i]=$c;
                $rank[$j]=$c1;
            }
        }
    }
    return $rank;
}

function repeatcount($rank){
    $u=array();
    $cnt=array();
    for($i=0; $i<count($rank); $i++){
        $c=0;
        for($j=0; $j<count($rank); $j++){
            if($i!=0){
                if($rank[$i]!=$rank[$i-1] && $rank[$i]==$rank[$j]){
                    
                        $c+=1;
                        if($c>1 && $c<3){
                            $u[$i]=$rank[$i];
                            $cnt[$i]=$c;
                        }
                        else if($c>2){
                            $cnt[$i]=$c;
                        } 
                    
                }
                
            }
            else{
                if($rank[$i]==$rank[$j]){
                    $c+=1;
                    if($c>1 && $c<3){
                        $u[$i]=$rank[$i];
                        $cnt[$i]=$c;
                    }
                    else if($c>2){
                        $cnt[$i]=$c;
                    }
                }
            }
        }
    }
    return array($u, $cnt);
}

function histogram($array){
    $sarray=sorting($array);
    $n=count($sarray);
    $newarray=array();
    $cnt=array();
    for($i=0; $i<$n; $i++){
        $c=0;
        for($j=0; $j<$n; $j++){
            if($i!=0){
                if($sarray[$i]!=$sarray[$i-1] && $sarray[$i]==$sarray[$j]){
                    $c+=1;
                    $cnt[$i]=$c;
                    $newarray[$i]=$sarray[$i];
                }
            }
            else{
                if($sarray[$i]==$sarray[$j]){
                    $c+=1;
                    $cnt[$i]=$c;
                    $newarray[$i]=$sarray[$i];
                }
            }
        }
    }
    return array(array_values($newarray), array_values($cnt));
}