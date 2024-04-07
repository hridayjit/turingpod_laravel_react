<?php

    namespace StatisticsLibrary\Lib;

    //statistics class
    class DistributionLibrary{

        //gamma function
        public function gamma($n){//$n = any real number
            if ($n==1){
                return 1;
            }
            elseif ($n<1){
                $y=0.5772156649;
                $alpha=0.35;
                $g1=$alpha*((1/$n)-$y+(($n*(6*$y*$y+pi()*pi()))/12));
                $g2=(12*$y+(pi()*pi()-6*$y*$y)*$n)/(12*$y+(pi()*pi()+6*$y*$y)*$n);
                $g3=(1/$n)*$g2*(1-$alpha);
                return $g1+$g3;
            }
            elseif($n>1){
                $x=$n-1;
                return sqrt(2*pi()*$x)*(pow($x, $x)/exp($x))*(pow($x*sinh(1/$x), $x/2)*exp(7/(324*$x*$x*$x*(35*$x*$x+33))));
            }
        }

        //plot z cumulative
        /*function plotZCDF($min_num, $max_num, $num_iterations){
            $unit = ($max_num - $min_num)/$num_iterations;
            $x_array=array();
            $cdf_array =array();
            for($i=0; $i<$num_iterations; $i++){
                $x = $min_num + ($i * $unit);
                $zcdf = $this->zCumDistFunc($x, 18);
                $x_array[] = $x;
                $cdf_array[] = $zcdf;
            }
            var_dump($x_array, $cdf_array);
        }*/
        
        //cumulative distribution function value
        public function zCumDistFunc($z, $num_iterations){//$z = a value between two extreme values (eg. -3.5 and 3.49), $num_iterations=18 (ideally)
            $iter=0;
            for ($n=0; $n<$num_iterations+1; $n++){
                $iter+=(pow(-1, $n)*pow($z, (2*$n+1)))/(pow(2, $n)*$this->gamma($n+1)*(2*$n+1));
            }
            return (0.5+(1/sqrt(2*pi()))*$iter);
        }

        //function probitFunc or inverse z-cdf value
        public function probitZ($p){//$p = probability value between 0 and 1 excluding 0 and 1
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

        //z PDF CDF arrays
        public function zPdfCdfArray(){
            $zmin=$this->probitZ(0.001);
            $zmax=$this->probitZ(0.999);
            $z=array();
            $pdf=array();
            $cdf=array();
            $zdiff=($zmax-$zmin)/1000;
            for($i=0; $i<1000; $i++){
                $z[$i]=$zmin+$zdiff*$i;
                $pdf[$i]=exp((-$z[$i]*$z[$i])/2)/sqrt(2*pi());
                $cdf[$i]=$this->zCumDistFunc($z[$i], 18);
            }
            return array($pdf, $cdf, $z);
        }

        //student t-distribution critical value or ttab
        public function studentTCriticalValue($alpha, $v){//$alpha = value between 0 and 1 excluding 0 and 1
            if($v!=1){
                $g=($v-1.5-(0.1/$v)+(0.5825/($v*$v)))/(($v-1)*($v-1));
                $z=$this->probitZ($alpha);
                return sqrt($v*exp($z*$z*$g)-$v);
            }
            else{
                return 6.314;
            }
        }
        //student-t pdf Array
        public function studentTPdfArray($v){
            $tmin=-$this->studentTCriticalValue(0.001, $v);
            $tmax=$this->studentTCriticalValue(0.999, $v);
            $t=array();
            $pdf=array();
            $tdiff=($tmax-$tmin)/1000;
            for ($i=0; $i<1000; $i++){
                $t[$i]=$tmin+$tdiff*$i;
                $pdf[$i]=($this->gamma(($v+1)/2)/(sqrt($v*pi())*$this->gamma($v/2)))*pow((1+($t[$i]*$t[$i])/$v), -(($v+1)/2));
            }
            return array($pdf, $t);
        }

        //student-t cdf Array
        public function studentTCdfArray($v){
            $pdf1=$this->studentTPdfArray($v);
            $pdf=$pdf1[0];
            $t=$pdf1[1];
            $cdf=array();
            $c=count($pdf);
            $sum=0;
            for($i=0; $i<$c; $i++){
                $sum+=$pdf[$i];
                $cdf[$i]=$sum;
            }
            return array($cdf, $t);
        }

        //chi-sq test initialW value
        private function initialW($n, $f, $x){
            $alph=$f/2;
            $y=$x/2;
            if ($x<$f){
                $T=array();
                $T[$n]=($y*(1-$n-$alph))/(($alph+2*$n-1+$n*$y)/($alph+2*$n));
                for($k=$n-1; $k>1; $k--){
                    $T[$k]=($y*(1-$k-$alph))/(($alph+2*$k-1+$k*$y)/($alph+2*$k+$T[$k+1]));
                }
                $cfl=1-$y/($alph+1+$y/($alph+2+$T[2]));
                $iyalph=$alph*log($y)-$y-log($this->gamma($alph+1))-log($cfl);
                return exp($iyalph);
            }
            else if ($x>=$f){
                $T=array();
                $T[$n]=($n-$alph)/($y+$n);
                for($k=$n-1; $k>1; $k--){
                    $T[$k]=($k-$alph)/($y+$k/(1+$T[$k+1]));
                }
                $cfu=1+(1-$alph)/($y+1/(1+$T[2]));
                $iyalph=($alph-1)*log($y)-$y-log($this->gamma($alph))-log($cfu);
                return 1-exp($iyalph);
            }
        }

        //chi-sq pdf
        public function pdfChiSquareValue($chi, $f, $ncp){
            $N=50;
            $lam=$ncp/2;
            $P=array();
            $W=array();
            $S=array();
            $P[0]=exp(-$lam);
            $W[0]=$this->initialW(25, $f, $chi);
            $lnSin=(($f-2)/2)*log($chi)-($chi/2)-(($f-2)/2)*log(2)-log($this->gamma($f/2));
            $S[0]=exp($lnSin);
            $sum=$P[0]*$W[0];
            for ($i=1; $i<$N+1; $i++){
                $P[$i]=($lam/$i)*$P[$i-1];
                $S[$i]=($chi/($f+2*$i-2))*$S[$i-1];
                $W[$i]=-$S[$i]+$W[$i-1];
                $sum+=$P[$i]*$W[$i];
            }
            return 1-$sum;
        }
        
        
        //quantile or inverse-cdf
        public function quantileChiSquareValue($p, $f){
            $z=$this->probitZ(1-$p);
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
                    $z1=$this->probitZ(($p)/2);
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
                    $z1=$this->probitZ(($p)/2);
                    $result= $z1*$z1;
                }
            }
            return $result;
        }

        //F statistics Value
        public function fValue($p, $v1, $v2){
            $z=$this->probitZ(1-$p);
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
                $x-=($fx/$fxd);
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
        
        //F CDF value
        public function fCdfValue($f, $v1, $v2){
            $n1=(2*$v2+(($v1*$f)/3)+$v1-2)*$f;
            $n2=(2*$v2)+((4*$v1*$f)/3);
            $z=(pow($n1/$n2, 1/3)-(1-(2/(9*$v1))))/pow((2/(9*$v1)), 0.5);
            return $this->zCumDistFunc($z, 18);
        }

        //binomial distribution
        public function cumulativePdfBinomial($p, $n, $a, $b){//$p for probability of success, $n for total number of trial, $a=0(generally) for starting range of trial, $b ending range of trial  
            $pdf=array();
            $cumpdf=array();
            $sum=0;
            for($i=$a; $i<$b; $i++){
                $pdf[$i]=($this->gamma($n+1)/($this->gamma(($n-$i)+1)*$this->gamma($i+1)))*pow($p, $i)*pow((1-$p), ($n-$i));
                $sum+=$pdf[$i];
                $cumpdf[$i]=$sum;
            }
            return array($pdf, $cumpdf);
        }

        public function criticalXForBinomial($alph, $p, $n, $xinit){//$xinit generally 0, $alph is derived from significance level, $p is success probability, $n is the no. of trials
            $sum=0;
            $alpha1=0;
            while($alpha1<=$alph){
                $sum+=(($this->gamma($n+1)/($this->gamma(($n-$xinit)+1)*$this->gamma($xinit+1)))*pow($p, $xinit)*pow((1-$p), ($n-$xinit)));
                $alpha1=$sum;
                $xinit+=1;
            }
            return $xinit-1;
        }

        //poisson distribution
        public function poissonPdf($lambda){
            $pdf=array();
            $cumpdf=array();
            $prob=0;
            $i=0;
            while($prob<0.9999){
                $prob+=((pow($lambda, $i)*exp(-$lambda))/$this->gamma($i+1));
                $cumpdf[$i]=$prob;
                $i+=1;
            }
            $n=$i-1;
            for($j=0; $j<=$n; $j++){
                $pdf[$j]=((pow($lambda, $j)*exp(-$lambda))/$this->gamma($j+1));
            }
            return array($pdf, $cumpdf);
        }

        public function criticalXForPoisson($alph, $lambda, $xinit){
            $alpha1=0;
            while($alpha1<=$alph){
                $alpha1+=((pow($lambda, $xinit)*exp(-$lambda))/$this->gamma($xinit+1));
                $xinit+=1;
            }
            return $xinit-1;
        }

    }

?>