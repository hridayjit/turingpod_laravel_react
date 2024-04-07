<?php
require "statslibrary.php";

//--------------binomial hypothesis------------------------------------------
function cumpdfbin($p, $n, $a, $b){
    $pdf=array();
    $cumpdf=array();
    $sum=0;
    for($i=$a; $i<$b; $i++){
        $pdf[$i]=(gamma($n+1)/(gamma(($n-$i)+1)*gamma($i+1)))*pow($p, $i)*pow((1-$p), ($n-$i));
        $sum=$sum+$pdf[$i];
        $cumpdf[$i]=$sum;
    }
    return array($pdf, $cumpdf);
}

function criticalx($alph, $p, $n, $xinit){
    $sum=0;
    $alpha1=0;
    while($alpha1<=$alph){
        $sum=$sum+((gamma($n+1)/(gamma(($n-$xinit)+1)*gamma($xinit+1)))*pow($p, $xinit)*pow((1-$p), ($n-$xinit)));
        $alpha1=$sum;
        $xinit+=1;
    }
    return $xinit-1;
}

function binomial ($arr){
    $n=(float)$arr[1][0];
    $p=(float)$arr[1][1];
    $x=(float)$arr[1][2];
    $alpha=(float)$arr[1][3];
    $cumpdf=cumpdfbin($p, $n, 0, ($n+1));
    
    $prob=0;
    if($arr[2]=="lt"){
        $cumlt=cumpdfbin($p, $n, 0, ($x+1));
        $prob=$cumlt[1][$x];
        
        if($prob>=$alpha){
            $text="The sample data is unbiased. Thus Alternative hypothesis is rejected";
        }
        elseif($prob<$alpha){
            $text="The sample data is biased. Alternative hypothesis is accepted";
        }
        $xalph=criticalx($alpha, $p, $n, 0);
        return array($arr, $text, $prob, $cumpdf, $cumlt, $xalph);
    }
    else if($arr[2]=="gt"){
        $cumgt=cumpdfbin($p, $n, 0, (($x-1))+1);
        //cumulative greater than or equal to x
        $prob=1-$cumgt[1][$x-1];
        
        if($prob>=$alpha){
            $text="The sample data is unbiased. Thus Alternative hypothesis is rejected";
        }
        elseif($prob<$alpha){
            $text="The sample data is biased. Alternative hypothesis is accepted";
        }

        $xalph=criticalx($alpha, $p, $n, 0);
        return array($arr, $text, $prob, $cumpdf, $cumgt, $xalph);
    }
}
//------------poisson hypothesis----------------------------------------------------
function popdf($lambda){
    $pdf=array();
    $cumpdf=array();
    $prob=0;
    $i=0;
    while($prob<0.9999){
        $prob=$prob+((pow($lambda, $i)*exp(-$lambda))/gamma($i+1));
        $cumpdf[$i]=$prob;
        $i+=1;
    }
    $n=$i-1;
    for($j=0; $j<=$n; $j++){
        $pdf[$j]=((pow($lambda, $j)*exp(-$lambda))/gamma($j+1));
    }
    return array($pdf, $cumpdf);
}

function xpocrit($alph, $lambda, $xinit){
    $alpha1=0;
    while($alpha1<=$alph){
        $alpha1=$alpha1+((pow($lambda, $xinit)*exp(-$lambda))/gamma($xinit+1));
        $xinit+=1;
    }
    return $xinit-1;
}

function poisson($arr){
    $lambda=(float)$arr[1][0];
    $x=(float)$arr[1][1];
    $alpha=(float)$arr[1][2];
    $prob=0;

    if($arr[2]=="lt"){
        for($i=0; $i<$x+1;$i++){
            $prob=$prob+((pow($lambda, $i)*exp(-$lambda))/gamma($i+1));
        }
        if($prob>=$alpha){
            $text="There is no difference in means. Alternative hypothesis is rejected";
        }
        else if($prob<$alpha){
            $text="There is a difference in means. Alternative hypothesis is accepted";
        }
        return array($arr, $text, $prob, popdf($lambda), xpocrit($alpha, $lambda, 0));
    }
    else if($arr[2]=="gt"){
        for($i=0; $i<$x; $i++){
            $prob=$prob+((pow($lambda, $i)*exp(-$lambda))/gamma($i+1));
        }
        $prob=1-$prob;
        if($prob>=$alpha){
            $text="There is no difference in means. Alternative hypothesis is rejected";
        }
        else if($prob<$alpha){
            $text="There is a difference in means. The Alternative hypothesis is accepted";
        }
        $xright=xpocrit(1-$alpha, $lambda, 0);
    return array($arr, $text, $prob, popdf($lambda), $xright);
    }
}

//-------------------regression-------------------
function corrsig($arr){
    $corr1=rVal($arr[1], $arr[2]);
    $corr=(($corr1==1) ? 0.9999 : $corr1);
    $n=count($arr[1]);
    $tcal=($corr*sqrt($n-2))/(1-pow($corr, 2));
    $v=$n-2;
    $ttab=tValue(0.95, $v);
    if($tcal<0){
        $ttab=-$ttab;
    }
    $text1="The correlation between X-Y dataset is statistically not significant";
    $text2="The correlation between X-Y dataset is statistically significant";
    if($tcal<0 && $ttab<0){
        if ($tcal<$ttab){
            $text=$text2;
        }
        else if($tcal>=$ttab){
            $text=$text1;
        }
    }
    else{
        if ($tcal<=$ttab){
           $text=$text1;
        }
        else if($tcal>$ttab){
            $text=$text2;
        }
    }
    return array($text, $tcal, $ttab);
}

function polyRegression($arr){
    $deg=(int) filter_var($arr[0], FILTER_SANITIZE_NUMBER_INT);
    $arr1=array(array());
    for($i=0; $i<count($arr)-1; $i++){
        for($j=0; $j<count($arr[$i+1]); $j++){
            $arr1[$i][$j]=(float)$arr[$i+1][$j];
        }
    }
    $b=coffBestFit($deg, $arr1[0], $arr1[1]);
    $measures=regMeasures($deg, $arr1[0], $arr1[1]);
    $text=corrsig($arr);
    //var_dump($text);
    echo json_encode(array($arr, $deg, $b, $measures, $text));
}

function exlogRegression($arr){
    if ($arr[0]=="degex"){
        $deg=1;
        $ynew=array();
        for($i=0; $i<count($arr[2]); $i++){
            $ynew[$i]=log($arr[2][$i]);
        }
        $lcoff=coffBestFit($deg, $arr[1], $ynew);
        $b=array(exp($lcoff[0]), $lcoff[1]);
        $measures=regMeasures($deg, $arr[1], $ynew);
        $text=corrsig($arr);
        echo json_encode(array($arr, $deg, $b, $measures, $ynew, $text));
    }
    else if($arr[0]=="deglog"){
        $deg=1;
        $xnew=array();
        for ($i=0; $i<count($arr[1]); $i++){
            $xnew[$i]=log($arr[1][$i]);
        }
        $b=coffBestFit($deg, $xnew, $arr[2]);
        $measures=regMeasures($deg, $xnew, $arr[2]);
        $text=corrsig($arr);
        echo json_encode(array($arr, $deg, $b, $measures, $xnew, $text));
    }
}

//--------------------------interpolation---------------------
function interpolate($arr){
    $deg=count($arr[1])-1;
    $b=intConst($arr[1], $arr[2]);
    return array($arr, $deg, $b);
}

//----------one sample
function tpdf($v){
    $tmin=-tValue(0.001, $v);
    $tmax=tValue(0.999, $v);
    $t=array();
    $pdf=array();
    $tdiff=($tmax-$tmin)/1000;
    for ($i=0; $i<1000; $i++){
        $t[$i]=$tmin+$tdiff*$i;
        $pdf[$i]=(gamma(($v+1)/2)/(sqrt($v*pi())*gamma($v/2)))*pow((1+($t[$i]*$t[$i])/$v), -(($v+1)/2));
    }
    return array($pdf, $t);
}
//------------------------------------------------------------------------
function zpcdf(){
    $zmin=probit(0.001);
    $zmax=probit(0.999);
    $z=array();
    $pdf=array();
    $cdf=array();
    $zdiff=($zmax-$zmin)/1000;
    for($i=0; $i<1000; $i++){
        $z[$i]=$zmin+$zdiff*$i;
        $pdf[$i]=exp((-$z[$i]*$z[$i])/2)/sqrt(2*pi());
        $cdf[$i]=zcdf($z[$i]);
    }
    return array($pdf, $cdf, $z);
}

function zarr($ztab){
    $z=zpcdf()[2];
     $cz=count($z);
     if ($ztab>=0){
        $zcrit=$ztab+1;
        while($zcrit>=$ztab){
            $zcrit=$z[$cz-1];
            $cz-=1;
        }
     }
     else{
         $zcrit=$ztab-1;
         $i=0;
         while($zcrit<=$ztab){
            $zcrit=$z[$i];
            $i+=1;
         }
     }
    return $zcrit;
}
//--------------------------------------------------------------------------
function tcdf($v){
    $pdf1=tpdf($v);
    $pdf=$pdf1[0];
    $t=tpdf($v)[1];
    $cdf=array();
    $c=count($pdf);
    $sum=0;
    for($i=0; $i<$c; $i++){
        $sum=$sum+$pdf[$i];
        $cdf[$i]=$sum;
    }
    return array($cdf, $t);
}

 function tcrit($ttab, $v){
     $t=tpdf($v)[1];
     $ct=count($t);
     if ($ttab>=0){
        $tcrit=$ttab+1;
        while($tcrit>=$ttab){
            $tcrit=$t[$ct-1];
            $ct-=1;
        }
     }
     else{
         $tcrit=$ttab-1;
         $i=0;
         while($tcrit<=$ttab){
            $tcrit=$t[$i];
            $i+=1;
         }
     }
    return $tcrit;
 }

function oneSample($arr){
    if($arr[0]=="td"){
        $ntail=(int)$arr[2][0];
        $n=count($arr[1]);
        $v=$n-1;
        $sumt=0;
        $merr=0;
        for($i=0; $i<$n; $i++){
            $sumt=$sumt+$arr[1][$i];
        }
        $xbar=$sumt/$n;
        $popbar=(float)$arr[2][1];
        for($i=0; $i<$n; $i++){
            $merr=$merr+pow(($arr[1][$i]-$xbar), 2);
        }
        $stdv=sqrt($merr/($n-1));
        $tcal=(($xbar-$popbar)*sqrt($n))/$stdv;
        $tcalarr=tcrit($tcal, $v);
        if($ntail==1){
            if($arr[2][2]<=0.5){
                $p=1-$arr[2][2];
            }
            else{
                $p=$arr[2][2];
            }
            $ttab=tValue($p, $v);
        }
        else if($ntail==2){
            $sl=$arr[2][2]/2;
            if ($sl<=0.5){
                $p=1-$sl;
            }
            else{
                $p=$sl;
            }
            $ttab=tValue($p, $v);
        }
        if($tcal<0){
            $ttab=-$ttab;
        }
        $tcrit=tcrit($ttab, $v);
        $text1="There is no significant difference between sample and population mean. Alternate hypothesis is rejected.";
        $text2="The difference between sample and population mean is significant. Alternate hypothesis is accepted";
        if($tcal<0 && $ttab<0){
            if ($tcal<$ttab){
                $text=$text2;
            }
            else if($tcal>=$ttab){
                $text=$text1;
            }
        }
        else{
            if ($tcal<=$ttab){
               $text=$text1;
            }
            else if($tcal>$ttab){
                $text=$text2;
            }
        }
        $tpdf=tpdf($v);
        $tcdf=tcdf($v);
        return array($arr, $text, $tcal, $ttab, $xbar, $popbar, $stdv, $arr[2][2], $tcrit, $tpdf, $tcdf, $tcalarr);
    }
    //-------------------------------------------------------------
    else if($arr[0]=="zd"){
        $ntail=(int)$arr[2][0];
        $n=count($arr[1]);
        $sumt=0;
        for($i=0; $i<$n; $i++){
            $sumt=$sumt+$arr[1][$i];
        }
        $xbar=$sumt/$n;
        $popbar=(float)$arr[2][1];
        $stdv=(float)$arr[2][2];
        $zcal=(($xbar-$popbar)*sqrt($n))/$stdv;
        $zcalarr=zarr($zcal);
        
        if($ntail==1){
            if($arr[2][3]<=0.5){
                $p=1-$arr[2][3];
            }
            else{
                $p=$arr[2][3];
            }
            $ztab=probit($p);
        }
        else if($ntail==2){
            $sl=$arr[2][3]/2;
            if($sl<=0.5){
                $p=1-$sl;
            }
            else{
                $p=$sl;
            }
            $ztab=probit($p);
        }
        if($zcal<0){
            $ztab=-$ztab;
        }
        $zcrit=zarr($ztab);
        $text1="There is no significant difference between sample and population mean. Alternate hypothesis is rejected.";
        $text2="The difference between sample and population mean is significant. Alternate hypothesis is accepted";
        if($zcal<0 && $ztab<0){
            if ($zcal<$ztab){
                $text=$text2;
            }
            else if($zcal>=$ztab){
                $text=$text1;
            }
        }
        else{
            if ($zcal<=$ztab){
               $text=$text1;
            }
            else if($zcal>$ztab){
                $text=$text2;
            }
        }
        $zpcdf=zpcdf();
        $zpdf=array($zpcdf[0], $zpcdf[2]);
        $zcdf=array($zpcdf[1], $zpcdf[2]);
        return array($arr, $text, $zcal, $ztab, $xbar, $popbar, $stdv, $arr[2][3], $zcrit, $zpdf, $zcdf, $zcalarr);
    } 
}
//-----------------------Hypothesis (2 or more samples)-------

function ttest($arr){
    if($arr[0]=="ptt"){
        $n=count($arr[3]);
        $diff=array();
        for ($i=0; $i<$n; $i++){
            $diff[$i]=($arr[3][$i]-$arr[4][$i]);
        }
        $sum=sum($diff);
        $sumx=sum($arr[3]);
        $sumy=sum($arr[4]);
        
        $diffbar=$sum/$n;
        $xbar=$sumx/$n;
        $ybar=$sumy/$n;
        $sum1=0;
        $sum2=0;
        $sum3=0;
        for($k=0; $k<count($diff); $k++){
            $sum1=$sum1+pow(($diff[$k]-$diffbar), 2);
            $sum2=$sum2+pow(($arr[3][$k]-$xbar), 2);
            $sum3=$sum3+pow(($arr[4][$k]-$ybar), 2);
        }
        $stdv=sqrt($sum1/($n-1));
        $stdvx=sqrt($sum2/($n-1));
        $stdvy=sqrt($sum3/($n-1));
       
        $df=$n-1;
        $tcal=($diffbar*sqrt($n))/$stdv;
        $sl1=$arr[1];
        if ($arr[2]==1){
            $sl=$sl1;
        }
        else if($arr[2]==2){
            $sl=$sl1/2;
        }
        if ($sl<=0.5){
            $p=1-$sl;
        }
        else {
            $p=$sl;
        }
        $ttab=tValue($p, $df);
        if($tcal<0){
            $ttab=-$ttab;
        }
       
        //-------------
        $text1="Samples are not statistically significant. Alternate hypothesis is rejected.";
        $text2="Samples are statistically significant. Alternate hypothesis is accepted";
        if($tcal<0 && $ttab<0){
            if ($tcal<$ttab){
                $text=$text2;
            }
            else if($tcal>=$ttab){
                $text=$text1;
            }
        }
        else{
            if ($tcal<=$ttab){
               $text=$text1;
            }
            else if($tcal>$ttab){
                $text=$text2;
            }
        }
        
        $sterrorx=($stdvx/sqrt($n))*2;
        $sterrory=($stdvy/sqrt($n))*2;
        $sterror=($stdv/sqrt($n));
        $means=array($xbar, $ybar, $diffbar);
        $stdvs=array($stdvx, $stdvy, $stdv);
        $sterrors=array($sterrorx, $sterrory, $sterror);
        $correlation=regMeasures(1, $arr[3], $arr[4]);
        $tcrit=tcrit($ttab, $df);
        $tcalarr=tcrit($tcal, $df);
        $tpdf=tpdf($df);
        
        //outputs arr text hypothesis, t calculated, t tabulated, average of difference between paired elements, standard deviation. 
        return array($arr, $text, $tcal, $ttab, $means, $stdvs, $sterrors, $correlation, $df, $n, $tcrit, $tcalarr, $tpdf, $diff);
    }
    else if($arr[0]=="utt"){
        $n1=count($arr[3]);
        $n2=count($arr[4]);
        
        $sum1=sum($arr[3]);
        $sum2=sum($arr[4]);
        $xbar=$sum1/$n1;
        $ybar=$sum2/$n2;
        $diffbar=$xbar-$ybar;
        $dx=0;
        for($i=0;$i<$n1;$i++){
            $dx=$dx+pow(($arr[3][$i]-$xbar), 2);
        }
        $dy=0;
        for ($j=0; $j<$n2; $j++){
            $dy=$dy+pow(($arr[4][$j]-$ybar), 2);
        }
        $df=$n1+$n2-2;
        $stdv=sqrt(($dx+$dy)/$df);
        $stdvx=sqrt($dx/($n1-1));
        $stdvy=sqrt($dy/($n2-1));
        
        $tcal=($xbar-$ybar)/($stdv*sqrt((1/$n1)+(1/$n2)));

        $sl1=$arr[1];
        if ($arr[2]==1){
            $sl=$sl1;
        }
        else if($arr[2]==2){
            $sl=$sl1/2;
        }
        if ($sl<=0.5){
            $p=1-$sl;
        }
        else {
            $p=$sl;
        }
        $ttab=tValue($p, $df);
        if($tcal<0){
            $ttab=-$ttab;
        }
        $text1="Samples are not statistically significant. Alternate hypothesis is rejected.";
        $text2="Samples are statistically significant. Alternate hypothesis is accepted";
        if($tcal<0 && $ttab<0){
            if ($tcal<$ttab){
                $text=$text2;
            }
            else if($tcal>=$ttab){
                $text=$text1;
            }
        }
        else{
            if ($tcal<=$ttab){
               $text=$text1;
            }
            else if($tcal>$ttab){
                $text=$text2;
            }
        }
        
        $sterrorx=($stdvx/sqrt($n1))*2;
        $sterrory=($stdvy/sqrt($n2))*2;
        $sterror=sqrt((($stdvx*$stdvx)/$n1)+(($stdvy*$stdvy)/$n2));
        
        $stdvs=array($stdvx, $stdvy, $stdv);
        $sterrors=array($sterrorx, $sterrory, $sterror);
        $means=array($xbar, $ybar, $diffbar);
        $tcrit=tcrit($ttab, $df);
        $tcalarr=tcrit($tcal, $df);
        $tpdf=tpdf($df);
        //outputs arr, t hypothesis text, t calculated, t tabulated, average of x, average of y, standard deviation, degree of freedom.
        return array($arr, $text, $tcal, $ttab, $means, $stdvs, $sterrors, $n1, $df, $n2, $tcrit, $tcalarr, $tpdf);
    }
}

function owanova($arr){
    $arrcount=count($arr);
    $n=array();
    $nelements=0;
    for($i=0; $i<($arrcount-2); $i++){
        $n[$i]=count($arr[$i+2]);
        $nelements=$nelements+$n[$i];
    }
    $sum=0;
    for($i=0; $i<($arrcount-2); $i++){
        for($j=0; $j<$n[$i]; $j++){
            $sum=$sum+$arr[2+$i][$j];
        }
    }
    $correction=pow($sum, 2)/$nelements;
    //
    $sum1=0;
    for($i=0; $i<($arrcount-2); $i++){
        for($j=0; $j<$n[$i]; $j++){
            $sum1=$sum1+pow($arr[2+$i][$j], 2);
        }
    }
    $sst=$sum1-$correction;
    //
    $sum3=0;
    for($i=0; $i<($arrcount-2); $i++){
        $sum2=0;
        for($j=0; $j<$n[$i]; $j++){
            $sum2=$sum2+$arr[2+$i][$j];
        }
        $sum3=$sum3+(pow($sum2, 2)/$n[$i]);
    }
    
    $ssa=$sum3-$correction;
    $ssw=$sst-$ssa;
    $mssa=$ssa/(($arrcount-2)-1);
    $mssw=$ssw/($nelements-($arrcount-2));
    $F=$mssa/$mssw;
    $cdf=fcdf($F, (($arrcount-2)-1), ($nelements-($arrcount-2)));
    if($cdf<=(1-$arr[1])){
        $text="There is no significant effect of the factor on the outcome. Alternate hypothesis is rejected";
    }
    else{
        $text="There is a significant effect of the factor on the outcome. Alternate hypothesis is accepted";
    }

    $ss=array($ssa, $ssw, $sst);
    $mss=array($mssa, $mssw);
    $sterr=array();
    $means=array();
    $stdvs=array();
    for($j=0; $j<($arrcount-2); $j++){
        $sterr[$j]=meansterr($arr[2+$j])[2];
        $means[$j]=meansterr($arr[2+$j])[0];
        $stdvs[$j]=meansterr($arr[2+$j])[1];
    }
    //table outputs $arr, hypothesis text, F statistics, cdf F statistics, mean sum of squares between, mean sum of squares within.
   return array($arr, $text, $F, 1-$cdf, $n, $nelements, $ss, $mss, $means, $stdvs, $sterr, $nelements); 
}

function meansterr($arr){
    $n=count($arr);
    $sum=0;
    for($i=0; $i<$n; $i++){
        $sum=$sum+$arr[$i];
    }
    $mean=$sum/$n;
    $stdv=stdev($arr);
    $sterror=($stdv/sqrt($n))*2;
    return array($mean, $stdv, $sterror);
}

function anova($arr){
    if ($arr[0]=="rma"){
        if (!is_array($arr[2])){
            $arrcount=count($arr);
            $ngrp=$arr[2]*($arrcount-3);
            $n=count($arr[3]);
            $grpcount=(($arrcount-3)*$n)/$ngrp;
            if($grpcount % 1==0){
                $sum=0;
                for($i=0; $i<($arrcount-3); $i++){
                    for($j=0; $j<$n; $j++){
                        $sum=$sum+$arr[3+$i][$j];
                    }
                }
                $correction=pow($sum, 2)/(($arrcount-3)*$n);
                $sum1=0;
                for($i=0; $i<($arrcount-3); $i++){
                    for($j=0; $j<$n; $j++){
                        $sum1=$sum1+pow($arr[3+$i][$j], 2);
                    }
                }
                $sst=$sum1-$correction;
                $sum2=0;
                for($i=0; $i<($arrcount-3); $i++){
                    $sum3=0;
                    for($j=0; $j<$n; $j++){
                        $sum3=$sum3+$arr[3+$i][$j];
                    }
                    $sum2=$sum2+pow($sum3, 2);
                }
                $ssc=($sum2/$n)-$correction;
                //
                $nIteration=$n/$grpcount;
                $sum5=0;
                for($k=0; $k<$nIteration; $k++){
                    $sum4=0;
                    for ($i=0; $i<($arrcount-3); $i++){
                        for($j=$k*$grpcount; $j<$grpcount*($k+1); $j++){
                            $sum4=$sum4+$arr[3+$i][$j];
                        } 
                    }
                    $sum5=$sum5+pow($sum4, 2);
                }
                $ssr=($sum5/($grpcount*($arrcount-3)))-$correction;
                $grps=array(array());
                $sum7=0;
                $nIteration=$arr[2];
                for($k=0; $k<$nIteration; $k++){
                    for($i=0; $i<($arrcount-3); $i++){
                        $sum6=0;
                        $counter=0;
                        for($j=$k*$grpcount; $j<$grpcount*($k+1); $j++){
                            $sum6=$sum6+$arr[3+$i][$j];
                            $grps[$i+($arrcount-3)*$k][$counter]=$arr[3+$i][$j];
                            $counter+=1;
                        }
                        $sum7=$sum7+pow($sum6, 2);
                    }
                }
                $ssg=($sum7/$grpcount)-$correction-$ssc-$ssr;
                $sse=$sst-$ssc-$ssr-$ssg;
                $rows=$arr[2];
                $col=$arrcount-3;
                $mssc=$ssc/($col-1);
                $mssr=$ssr/($rows-1);
                $mssg=$ssg/(($col-1)*($rows-1));
                $msse=$sse/($col*$rows*($grpcount-1));
                $Frow=$mssr/$msse;
                $Fcol=$mssc/$msse;
                $Fint=$mssg/$msse;
                $cdfrow=fcdf($Frow, ($rows-1), ($col*$rows*($grpcount-1)));
                $cdfcol=fcdf($Fcol, ($col-1), ($col*$rows*($grpcount-1)));
                $cdfint=fcdf($Fint, (($col-1)*($rows-1)), ($col*$rows*($grpcount-1)));
                if($cdfrow<=(1-$arr[1])){
                    $textrow="There is no significant effect of between subject factor on the outcome. Alternate hypothesis is rejected";
                }
                else{
                    $textrow="There is a significant effect of between subject factor on the outcome. Alternate hypothesis is accepted";
                }
                if ($cdfcol<=(1-$arr[1])){
                    $textcol="There is no significant effect of within subject factor on the outcome. Alternate hypothesis is rejected";
                }
                else{
                    $textcol="There is a significant effect of within subject factor on the outcome. Alternate hypothesis is accepted";
                }
                if ($cdfint<=(1-$arr[1])){
                    $textint="There is no significant interaction between within and between subject factors. Alternate hypothesis is rejected";
                }
                else{
                    $textint="There is a significant interaction between within and between subject factors. Alternate hypothesis is accepted";
                }
                $sterr=array();
                $means=array();
                $stdvs=array();
                for($j=0; $j<$ngrp; $j++){
                    $sterr[$j]=meansterr($grps[$j])[2];
                    $means[$j]=meansterr($grps[$j])[0];
                    $stdvs[$j]=meansterr($grps[$j])[1];
                }
                $ss=array($ssc, $ssr, $ssg, $sse, $sst);
                
                //table outputs arr, row factor effect hypothesis, column factor effect hypothesis, interaction effect hypothesis, F statistics row, F statistics column, cdf F statistics row, cdf F statistics column, cdf F statistics interaction, mean sum of squares values..........
                return array($arr, $textrow, $textcol, $textint, $Frow, $Fcol, $Fint, 1-$cdfrow, 1-$cdfcol, 1-$cdfint, $mssc, $mssr, $mssg, $msse, $sterr, $stdvs, $means, $grpcount, $ss);
            }
        }
        else{
            return owanova($arr);
        }
    }
    else if($arr[0]=="owa"){
        return owanova($arr);
    }
}

function stdev($arr){
    $n=count($arr);
    $sum=0;
    for($i=0; $i<$n; $i++){
        $sum=$sum+$arr[$i];
    }
    $avg=$sum/$n;
    $sum1=0;
    for($i=0; $i<$n; $i++){
        $sum1=$sum1+pow($arr[$i]-$avg, 2);
    }
    return sqrt($sum1/($n-1));
}

function wilcoxonSign($arr){
    $n=count($arr[3]);
    $diff1=array();
    for($i=0; $i<$n; $i++){
        $diff1[$i]=$arr[3][$i]-$arr[4][$i];
    }
    $diff=array_values(array_filter($diff1));
    $n1=count($diff);
    $absdiff=array();
    for($i=0; $i<$n1; $i++){
        if($diff[$i]<0){
            $absdiff[$i]=-1*$diff[$i];
        }
        else{
            $absdiff[$i]=$diff[$i];
        }
    }
    $rank=convertToRank($absdiff);
    $sumplus=0;
    $suminus=0;
    for($i=0; $i<$n1; $i++){
        if ($diff[$i]<0){
            $suminus=$suminus+$rank[$i];
        }
        else{
            $sumplus=$sumplus+$rank[$i];
        }
    }

    $minsum=min($sumplus, $suminus);
    
    $numerator=$minsum-((1/4)*$n1*($n1+1));
    if ($numerator<0){
        $numerator=-1*$numerator;
    }
    $z=$numerator/sqrt(($n1*($n1+1)*(2*$n1+1))/24);
    if ($arr[2]==1){
        $p=$arr[1];
    }
    else if($arr[2]==2){
        $p=$arr[1]/2;
    }
    $p1=1-$p;
    $ztab=probit($p1);


    if ($z>$ztab){
        $text="Significant difference between paired treatments. Alternate hypothesis is accepted.";
    }
    else{
        $text="No significant difference between paired treatments. Alternate hypothesis is rejected";
    }
    
    $bars=histogram($diff)[0];
    $frequency=histogram($diff)[1];
    array_push($bars, 0);
    array_push($frequency, 0);
    //boxplot
    $arr1=sorting($arr[3]);
    $arr2=sorting($arr[4]);
    $med=($n/2);
    $remainder=fmod($med, 1);
    if($remainder !=0){
        $medianpos=(($n+1)/2)-1;
        $q1posin=($medianpos-1)/2;
        if(fmod($q1posin, 1)==0){
            $q1pos=($medianpos-1)/2;
            $q3pos=$medianpos+(($n-$medianpos)/2);
            $iqrarr1=$arr1[$q3pos]-$arr1[$q1pos];
            $outlier1arr1=$arr1[$q1pos]-1.5*$iqrarr1;
            $outlier3arr1=$arr1[$q3pos]+1.5*$iqrarr1;
            $iqrarr2=$arr2[$q3pos]-$arr2[$q1pos];
            $outlier1arr2=$arr2[$q1pos]-1.5*$iqrarr2;
            $outlier3arr2=$arr2[$q3pos]+1.5*$iqrarr2;
            $positions=array($medianpos, $q1pos, $q3pos);
        }
        else if(fmod($q1posin, 1)!=0){
            $q1pos=array(($n-5)/4, ($n-1)/4);
            $q3pos=array((3*($n-1))/4, (3*$n+1)/4);
            $q1arr1=($arr1[$q1pos[0]]+$arr1[$q1pos[1]])/2;
            $q3arr1=($arr1[$q3pos[0]]+$arr1[$q3pos[1]])/2;
            $iqrarr1=$q3arr1-$q1arr1;
            $outlier1arr1=$q1arr1-1.5*$iqrarr1;
            $outlier3arr1=$q3arr1+1.5*$iqrarr1;
            $q1arr2=($arr2[$q1pos[0]]+$arr2[$q1pos[1]])/2;
            $q3arr2=($arr2[$q3pos[0]]+$arr2[$q3pos[1]])/2;
            $iqrarr2=$q3arr2-$q1arr2;
            $outlier1arr2=$q1arr2-1.5*$iqrarr2;
            $outlier3arr2=$q3arr2+1.5*$iqrarr2;
            $positions=array($medianpos, $q1pos, $q3pos);
        }
    }
    else if($remainder==0){
        $medianpos=array(($n/2)-1, $n/2);
        if(fmod($n/2, 2)==0){
            $q1pos=array(($n/4)-1, $n/4);
            $q3pos=array(((3*$n)/4)-1, (3*$n)/4);
            $q1arr1=($arr1[$q1pos[0]]+$arr1[$q1pos[1]])/2;
            $q3arr1=($arr1[$q3pos[0]]+$arr1[$q3pos[1]])/2;
            $iqrarr1=$q3arr1-$q1arr1;
            $outlier1arr1=$q1arr1-1.5*$iqrarr1;
            $outlier3arr1=$q3arr1+1.5*$iqrarr1;
            $q1arr2=($arr2[$q1pos[0]]+$arr2[$q1pos[1]])/2;
            $q3arr2=($arr2[$q3pos[0]]+$arr2[$q3pos[1]])/2;
            $iqrarr2=$q3arr2-$q1arr2;
            $outlier1arr2=$q1arr2-1.5*$iqrarr2;
            $outlier3arr2=$q3arr2+1.5*$iqrarr2;
            $positions=array($medianpos, $q1pos, $q3pos);
        }
        else if(fmod($n/2, 2)!=0){
            $q1pos=($n-2)/4;
            $q3pos=(3*$n-2)/4;
            $q1arr1=$arr1[$q1pos];
            $q3arr1=$arr1[$q3pos];
            $iqrarr1=$q3arr1-$q1arr1;
            $outlier1arr1=$q1arr1-1.5*$iqrarr1;
            $outlier3arr1=$q3arr1+1.5*$iqrarr1;
            $q1arr2=$arr2[$q1pos];
            $q3arr2=$arr2[$q3pos];
            $iqrarr2=$q3arr2-$q1arr2;
            $outlier1arr2=$q1arr2-1.5*$iqrarr2;
            $outlier3arr2=$q3arr2+1.5*$iqrarr2;
            $positions=array($medianpos, $q1pos, $q3pos);
        }
    }
    
    $iqrs=array($iqrarr1, $iqrarr2);
    $outlimitmin=array($outlier1arr1, $outlier1arr2);
    $outlimitmax=array($outlier3arr1, $outlier3arr2);
    $sortarrays=array($arr1, $arr2);
    $stats=array($minsum, $sumplus, $suminus);
    $stdvarr1=stdev($arr[3]);
    $stdvarr2=stdev($arr[4]);
    $stdv=array($stdvarr1, $stdvarr2);
    //outputs arr, hypothesis text, calculated z, tabulated z, minimum sum of ranks for difference values -ve or +ve (T), total number of ranks without zero.
    return array($arr, $text, $z, $ztab, $stats, $n, $bars, $frequency, $positions, $iqrs, $outlimitmin, $outlimitmax, $sortarrays, $stdv);
}

//-----------------------------------------------------------------------------------------------------

function boxplot($arr){
    $n=count($arr);
    $med=($n/2);
    $remainder=fmod($med, 1);
    if($remainder !=0){
        $medianpos=(($n+1)/2)-1;
        $medianval=$arr[$medianpos];
        $q1posin=($medianpos-1)/2;
        if(fmod($q1posin, 1)==0){
            $q1pos=($medianpos-1)/2;
            $q3pos=$medianpos+(($n-$medianpos)/2);
            $q1arr1=$arr[$q1pos];
            $q3arr1=$arr[$q3pos];
            $iqrarr1=$q3arr1-$q1arr1;
            $outlier1arr1=$q1arr1-1.5*$iqrarr1;
            $outlier3arr1=$q3arr1+1.5*$iqrarr1;
            $positions=array($medianpos, $q1pos, $q3pos);
        }
        else if(fmod($q1posin, 1)!=0){
            $q1pos=array(($n-5)/4, ($n-1)/4);
            $q3pos=array((3*($n-1))/4, (3*$n+1)/4);
            $q1arr1=($arr[$q1pos[0]]+$arr[$q1pos[1]])/2;
            $q3arr1=($arr[$q3pos[0]]+$arr[$q3pos[1]])/2;
            $iqrarr1=$q3arr1-$q1arr1;
            $outlier1arr1=$q1arr1-1.5*$iqrarr1;
            $outlier3arr1=$q3arr1+1.5*$iqrarr1;
            $positions=array($medianpos, $q1pos, $q3pos);
        }
    }
    else if($remainder==0){
        $medianpos=array(($n/2)-1, $n/2);
        $medianval=($arr[$medianpos[0]]+$arr[$medianpos[1]])/2;
        if(fmod($n/2, 2)==0){
            $q1pos=array(($n/4)-1, $n/4);
            $q3pos=array(((3*$n)/4)-1, (3*$n)/4);
            $q1arr1=($arr[$q1pos[0]]+$arr[$q1pos[1]])/2;
            $q3arr1=($arr[$q3pos[0]]+$arr[$q3pos[1]])/2;
            $iqrarr1=$q3arr1-$q1arr1;
            $outlier1arr1=$q1arr1-1.5*$iqrarr1;
            $outlier3arr1=$q3arr1+1.5*$iqrarr1;
            $positions=array($medianpos, $q1pos, $q3pos);
        }
        else if(fmod($n/2, 2)!=0){
            $q1pos=($n-2)/4;
            $q3pos=(3*$n-2)/4;
            $q1arr1=$arr[$q1pos];
            $q3arr1=$arr[$q3pos];
            $iqrarr1=$q3arr1-$q1arr1;
            $outlier1arr1=$q1arr1-1.5*$iqrarr1;
            $outlier3arr1=$q3arr1+1.5*$iqrarr1;
            $positions=array($medianpos, $q1pos, $q3pos);
        }
    }
    $quantiles=array($medianval, $q1arr1, $q3arr1);
    return array($positions, $iqrarr1, $outlier1arr1, $outlier3arr1, $quantiles);
}

function wilcoxonRank($arr){
    $k=count($arr)-3;
    $nData=array();
    for($i=0; $i<$k; $i++){
        $nData[$i]=count($arr[3+$i]);
    }
    $cumnData=array();
    $sum=0;
    for($k=0; $k<count($nData); $k++){
        $sum=$sum+$nData[$k];
        $cumnData[$k]=$sum;
    }
    $combined=array();
    $counter=0;
    for($i=0; $i<count($nData); $i++){
        for($j=0; $j<$nData[$i]; $j++){
            $combined[$counter]=$arr[3+$i][$j];
            $counter+=1;
        }
    }
    $rank=convertToRank($combined);
    $ranksum=array();
    for($i=0; $i<count($nData); $i++){
        $sum1=0;
        for($j=0; $j<$nData[$i]; $j++){
            if ($i==0){
                $sum1=$sum1+$rank[$j];
            }
            else {
                $sum1=$sum1+$rank[$j+$cumnData[$i-1]];
            } 
        }
        $ranksum[$i]=$sum1;
    }
    $sumrank1=$ranksum[0];
    $sumrank2=$ranksum[1];
    $u1=$sumrank1-($nData[0]*($nData[0]+1))/2;
    $u2=$sumrank2-($nData[1]*($nData[1]+1))/2;
    $u=min($u1, $u2);
    $znumerator=$u-(($nData[0]*$nData[1])/2);
    if ($znumerator<0){
        $znumerator=-1*$znumerator;
    }
    $rank1=sorting($rank);
    $valid=false;
    for($i=0; $i<count($rank1); $i++){
        if($i!=0 && $rank1[$i]==$rank1[$i-1]){
            $valid=true;
        }
    }
    if($valid){
        $unique=repeatcount($rank1)[0];
        $nunique=count($unique);
        $nrepeat1=array_values(repeatcount($rank1)[1]);
        $sum1=0;
        for($i=0; $i<$nunique; $i++){
            $nrepeat=$nrepeat1[$i];
            $sum1=$sum1+(pow($nrepeat, 3)-$nrepeat);
        }
        $term=$sum1/($cumnData[1]*($cumnData[1]-1));
        $sd=sqrt(((($nData[0]*$nData[1])/12)*(($cumnData[1]+1)-$term)));
    }
    else{
        $sd=sqrt((($nData[0]*$nData[1])/12)*($cumnData[1]+1));
    }
    $zstats=$znumerator/$sd;
    $sl=$arr[1];
    $tail=$arr[2];
    if($tail==1){
       $p=$sl; 
    }
    else if($tail==2){
        $p=$sl/2;
    }
    $p1=1-$p;
    $ztab=probit($p1);
    if($zstats<=$ztab){
        $text="No significant difference in treatment medians. Alternative hypothesis is rejected";
    }
    else{
        $text="Significant difference in treatment medians. Alternative hypothesis is accepted";
    }
    $arr1=sorting($arr[3]);
    $arr2=sorting($arr[4]);
    $arrX=boxplot($arr1);
    $arrY=boxplot($arr2);
    $n1=count($arr1);
    $n2=count($arr2);

    $iqrs=array($arrX[1], $arrY[1]);
    $outlimitmin=array($arrX[2], $arrY[2]);
    $outlimitmax=array($arrX[3], $arrY[3]);
    $sortarrays=array($arr1, $arr2);
    $stats=array($u, $u1, $u2);
    $stdvarr1=stdev($arr[3]);
    $stdvarr2=stdev($arr[4]);
    $stdv=array($stdvarr1, $stdvarr2);
    $pos=array($arrX[0], $arrY[0]);
    return array($arr, $text, $zstats, $ztab, $stats, $n1, $n2, $sd, $pos, $iqrs, $outlimitmin, $outlimitmax, $sortarrays, $stdv);
}
//---------------------------------------------------------------------------------------------------------------------------------------
function spearmanrank($arr){
    $x=$arr[1];
    $y=$arr[2];
    $rank1x=convertToRank($x);
    $rank1y=convertToRank($y);
    $diffsq=0;
    for($i=0; $i<count($rank1x); $i++){
        $diffsq=$diffsq+pow(($rank1x[$i]-$rank1y[$i]), 2);
    }
    $N=count($rank1x);
    $rankx=sorting($rank1x);
    $ranky=sorting($rank1y);
    $validx=false;
    $validy=false;
    for($i=0; $i<count($rankx); $i++){
        if($i!=0 && ($rankx[$i]==$rankx[$i-1]) && ($ranky[$i]==$ranky[$i-1])){
           $validx=true;
           $validy=true;
        }
        else if($i!=0 && ($rankx[$i]==$rankx[$i-1])){
            $validx=true;
        }
        else if($i!=0 && ($ranky[$i]==$ranky[$i-1])){
            $validy=true;
        }
    }
    $sumx=0;
    $sumy=0;
    if($validx && $validy){
        $nrepeatx=array_values(repeatcount($rankx)[1]);
        $nrepeaty=array_values(repeatcount($ranky)[1]);
        for($i=0; $i<count($nrepeatx); $i++){
            $sumx=$sumx+(pow($nrepeatx[$i], 3)-$nrepeatx[$i]);
        }
        for($i=0; $i<count($nrepeaty); $i++){
            $sumy=$sumy+(pow($nrepeaty[$i], 3)-$nrepeaty[$i]);
        }
        $term=($sumx+$sumy)/12;
        $rankterm=6*($diffsq+$term);
    }
    else if($validx){
        $nrepeatx=array_values(repeatcount($rankx)[1]);
        for($i=0; $i<count($nrepeatx); $i++){
            $sumx=$sumx+(pow($nrepeatx[$i], 3)-$nrepeatx[$i]);
        }
        $term=$sumx/12;
        $rankterm=6*($diffsq+$term);
    }
    else if($validy){
        $nrepeaty=array_values(repeatcount($ranky)[1]);
        for($i=0; $i<count($nrepeaty); $i++){
            $sumy=$sumy+(pow($nrepeaty[$i], 3)-$nrepeaty[$i]);
        }
        $term=$sumy/12;
        $rankterm=6*($diffsq+$term);
    }
    else{
        $rankterm=6*$diffsq;
    }
    $sc=1-$rankterm/(pow($N, 3)-$N);
    $ranks=array($rank1x, $rank1y);
    $b=coffBestFit(1, $rank1x, $rank1y);
    $rsq=regMeasures(1, $rank1x, $rank1y)[1];
    $stdv=regMeasures(1, $rank1x, $rank1y)[0];
    $param=array($b, $rsq, $stdv);
    return array($arr, $sc, $ranks, $param);
}

function kwallis($arr){
    $k=count($arr)-2;
    $nData=array();
    for($i=0; $i<$k; $i++){
        $nData[$i]=count($arr[2+$i]);
    }
    $cumnData=array();
    $sum=0;
    for($k=0; $k<count($nData); $k++){
        $sum=$sum+$nData[$k];
        $cumnData[$k]=$sum;
    }
    $combined=array();
    $counter=0;
    $arr1=array(array());
    $rankbp=array(array());
    $arrbp=array(array());
    for ($i=0; $i<count($nData); $i++){
        for($j=0; $j<$nData[$i]; $j++){
            $combined[$counter]=(int)$arr[2+$i][$j];
            $counter+=1;
            $arr1[$i][$j]=(int)$arr[2+$i][$j];
        }
        $rankbp[$i]=sorting($arr1[$i]);
        $arrbp[$i]=boxplot($rankbp[$i]);
    }
    $rank=convertToRank($combined);
    $ranksum=array();
    for($i=0; $i<count($nData); $i++){
        $sum1=0;
        for($j=0; $j<$nData[$i]; $j++){
            if ($i==0){
                $sum1=$sum1+$rank[$j];
            }
            else {
                $sum1=$sum1+$rank[$j+$cumnData[$i-1]];
            } 
        }
        $ranksum[$i]=$sum1;
    }
    $sum2=0;
    for ($k=0; $k<count($nData); $k++){
        $sum2=$sum2+(pow($ranksum[$k], 2)/$nData[$k]);
    }
    $hstat=((12*$sum2)/($cumnData[count($nData)-1]*($cumnData[count($nData)-1]+1)))-(3*($cumnData[count($nData)-1]+1));
    $df=count($nData)-1;
    $sl=$arr[1];
    $htab=qchisq($sl, $df);
    if ($hstat<=$htab){
        $text="There is no significant difference between the sample medians. Alternate hypothesis is rejected";
    }
    else if ($hstat>$htab){
        $text="There is a significant difference between the sample medians. Alternate hypothesis is accepted";
    }
    $boxplot=array($arr1, $rankbp, $arrbp);
    $stdvs=array();
    $means=array();
    $minobs=array();
    $maxobs=array();
    for($i=0; $i<count($arr1); $i++){
        $sumean=0;
        for($j=0; $j<$nData[$i]; $j++){
            $sumean=$sumean+$arr1[$i][$j];
        }
        $means[$i]=$sumean/count($arr1[$i]);
        $minobs[$i]=min($arr1[$i]);
        $maxobs[$i]=max($arr1[$i]);
        $stdvs[$i]=stdev($arr1[$i]);
        $expranksum=sum($ranksum)/count($arr1);
    }
    $descoutput=array($nData, $means, $stdvs, $minobs, $maxobs, $ranksum, $expranksum);
    $sig=pchisq($hstat, $df, 0);
    $infoutput=array($df, $sig);
    //outputs arr, hypothesis text, test statistics, tabulated chisq, rank sum array of columns.
    return array($arr, $text, $hstat, $htab, $ranksum, $boxplot, $descoutput, $infoutput);
}

function friedman ($arr){
    $ndataset=count($arr)-2;
    $n=count($arr[2]);
    $rowrank=array();
    $rank=array(array());
    $arr1=array(array());
    for($k=0; $k<$n; $k++){
        for($l=0; $l<$ndataset; $l++){
            $rowrank[$l]=$arr[2+$l][$k];
        }
        $rank[$k]=convertToRank($rowrank);
    }
    for($i=0; $i<$ndataset; $i++){
        for($j=0; $j<$n; $j++){
            $arr1[$i][$j]=(int)$arr[2+$i][$j];
        }
    }
    $ranktranspose=transpose($rank);
    $rankbp=array(array());
    $nonsort=array();
    $arrbp=array(array());
    //outputs
    $ranksum=array();
    $ranksumsq=array();
    //--------------
    $sum1=0;
    for ($i=0; $i<count($ranktranspose); $i++){
        $sum=0;
        for($j=0; $j<count($ranktranspose[0]); $j++){
            $nonsort[$j]=$ranktranspose[$i][$j];
            $sum=$sum+$nonsort[$j];
        }
        $ranksum[$i]=$sum;
        $ranksumsq[$i]=pow($sum, 2);
        $sum1=$sum1+$ranksumsq[$i];
        $rankbp[$i]=sorting($arr1[$i]);
        $arrbp[$i]=boxplot($rankbp[$i]);
    }
    $Ft=((12*$sum1)/($n*$ndataset*($ndataset+1)))-(3*$n*($ndataset+1));
    $sl=$arr[1];
    $chisq=qchisq($sl, $ndataset-1);
    if ($Ft>$chisq){
        $text="There is a significant difference between the ranked treatments. Alternate hypothesis is accepted";
    }
    else if($Ft<=$chisq){
        $text="There is no significant difference between the ranked treatments. Alternate hypothesis is rejected";
    }
    //descriptive outputs
    $stdvs=array();
    $means=array();
    $minobs=array();
    $maxobs=array();
    for($i=0; $i<$ndataset; $i++){
        $sumean=0;
        for($j=0; $j<$n; $j++){
            $sumean=$sumean+$arr[2+$i][$j];
        }
        $means[$i]=$sumean/count($arr[2+$i]);
        $minobs[$i]=min($arr[2+$i]);
        $maxobs[$i]=max($arr[2+$i]);
        $stdvs[$i]=stdev($arr[2+$i]);
    }
    $expranksum=sum($ranksum)/$ndataset;
    $descoutput=array($n, $means, $stdvs, $minobs, $maxobs, $ranksum, $expranksum, $ranksumsq);
    //inferential
    $df=$ndataset-1;
    $sig=pchisq($Ft, $df, 0);
    $infoutput=array($df, $sig);
    
    //outputs arr, hypothesis text, calculated chi sq, tabulated chi sq.
    return array($arr, $text, $Ft, $chisq, $arrbp, $rankbp, $descoutput, $infoutput);
}

//-------------------------------------------------------------------------------------------------------
function chipdf($chisqcal, $chisqtab, $df){
    $cprob=pchisq($chisqcal, $df, 0);
    if($cprob>=0.0001){
        $chiend=qchisq(0.0001, $df);
    }
    else if($cprob<0.0001){
        $chiend=qchisq($cprob-($cprob/2), $df);
    }
    $chiinit=qchisq(0.6984032686622538, $df);
    $chidiv=($chiend-$chiinit)/999;
    $chisq=array();
    $pdf=array();
    
    for($i=0; $i<1000; $i++){
        $chi=$chiinit+$chidiv*$i;
        $chisq[$i]=$chi;
        if ($chi>0.15){
            $term1=($df/2)-1;
            $term2=-1*($chi/2);
            $pdf[$i]=(pow($chi, $term1)*exp($term2))/(pow(2, $df/2)*gamma($df/2));
        }
        else{
            $pdf[$i]=0;
        }
    }
    $j=0;
    $ccal=$chisqcal-1;
    while($ccal<$chisqcal){
        $ccal=$chisq[$j];
        $j+=1;
    }
    $chical=$j-1;
    $k=0;
    $ctab=$chisqtab-1;
    while($ctab<$chisqtab){
        $ctab=$chisq[$k];
        $k+=1;
    }
    
    $chitab=$k-1;
    return array($pdf, $chisq, $ctab, $ccal, $chitab, $chical, $cprob);
    
}

function mcnemar($arr){
    $sl=$arr[1];
    $b=(int)($arr[3][0]);
    $c=(int)($arr[2][1]);
    $a=(int)($arr[2][0]);
    $d=(int)($arr[3][1]);
    $chisqcal=pow(($b-$c)-1, 2)/($b+$c);
    if($chisqcal==0){
        $chisqcal=0.15013825827986485;
    }
    $chisqtab=qchisq($sl, 1);
    if ($chisqcal<=$chisqtab){
        $text="No significant difference in treatment effectiveness. Alternate hypothesis is rejected";
    }
    else if($chisqcal>$chisqtab){
        $text="Significant difference in treatment effectiveness. Alternate hypothesis is accepted";
    }
    $elements=array($a, $b, $c, $d);
    $pdf=chipdf($chisqcal,$chisqtab, 1)[0];
    $chisq=chipdf($chisqcal,$chisqtab,1)[1];
    $ctab=chipdf($chisqcal, $chisqtab, 1)[2];
    $ccal=chipdf($chisqcal, $chisqtab, 1)[3];
    $chitab=chipdf($chisqcal, $chisqtab, 1)[4];
    $chical=chipdf($chisqcal, $chisqtab, 1)[5];
    $cp=chipdf($chisqcal,$chisqtab, 1)[6];

    // outputs arr, hypothesis text, calculated chi square, tabulated chi square.
return array($arr, $text, $chisqcal, $chisqtab, $elements, $pdf, $chisq, $ccal, $ctab, $chical, $chitab, $cp);
}

function chisqtest($arr){
    $nrow=count($arr[2]);
    $ncol=count($arr)-2;
    $freq=array(array());
    $sumcol=array();
    for($i=0; $i<$ncol; $i++){
        $sum=0;
        for($j=0; $j<$nrow; $j++){
            $sum=$sum+$arr[2+$i][$j];
            $freq[$i][$j]=$arr[2+$i][$j];
        }
        $sumcol[$i]=$sum;
    }
    $sumrow=array();
    for($i=0; $i<$nrow; $i++){
        $sum=0;
        for($j=0; $j<$ncol; $j++){
            $sum=$sum+$arr[2+$j][$i];
        }
        $sumrow[$i]=$sum;
    }
    $N=0;    
    for ($i=0; $i<count($sumcol); $i++){
        $N=$N+$sumcol[$i];
    }
    $exp=array(array());
    $expsumcol=array();
    for ($i=0; $i<$ncol; $i++){
        for($j=0; $j<$nrow; $j++){
            $exp[$i][$j]=($sumcol[$i]*$sumrow[$j])/$N;
        }
        $expsumcol[$i]=sum($exp[$i]);
    }
    $expsumrow=array();
    for($i=0; $i<$nrow; $i++){
        $sumex=0;
        for($j=0; $j<$ncol; $j++){
            $sumex=$sumex+$exp[$j][$i];
        }
        $expsumrow[$i]=$sumex;
    }
    $chisqcal=0;
    $csval=array(array());
    for($i=0; $i<$ncol; $i++){
        for($j=0; $j<$nrow; $j++){
            $chisqcal=$chisqcal+(pow(($arr[2+$i][$j]-$exp[$i][$j]), 2))/$exp[$i][$j];
            $csval[$j][$i]=(pow(($arr[2+$i][$j]-$exp[$i][$j]), 2))/$exp[$i][$j];
        }
    }
    $cstotarr=array();
    for($i=0; $i<$nrow; $i++){
        $cstotarr[$i]=sum($csval[$i]);
    }
    $sl=$arr[1];
    $df=($ncol-1)*($nrow-1);
    $chisqtab=qchisq($sl, $df);
    if ($chisqcal<=$chisqtab){
        $text="There is no significant relation between column and row factors. Alternate hypothesis is rejected";
    }
    else if($chisqcal>$chisqtab){
        $text="There is a significant relation between column and row factors. Alternate hypothesis is accepted";
    }
    $ndata=array($ncol, $nrow);
    $sums=array($sumcol, $sumrow);
    $expsums=array($expsumcol, $cstotarr);
    $expN=sum($cstotarr);
    $sig=pchisq($chisqcal, $df, 0);
    $inferential=array($df, $sl, $sig);
    //outputs arr, hypothesis text, calculated chi square, tabulated chi square, column summation array, row summation array, total number.
    return array($arr, $text, $chisqcal, $chisqtab, $N, $ndata, $sums, $freq, $exp, $expsums, $expN, $inferential);
}

function contingency($arr){
    $ncol=count($arr[1]);
    $nrow=count($arr)-1;
    $freq=array(array());
    $sumcol=array();
    for($i=0; $i<$nrow; $i++){
        $sum=0;
        for($j=0; $j<$ncol; $j++){
            $sum=$sum+$arr[1+$i][$j];
            $freq[$i][$j]=$arr[1+$i][$j];
        }
        $sumcol[$i]=$sum;
    }
    $sumrow=array();
    for($i=0; $i<$ncol; $i++){
        $sum=0;
        for($j=0; $j<$nrow; $j++){
            $sum=$sum+$arr[1+$j][$i];
        }
        $sumrow[$i]=$sum;
    }
    $N=0;    
    for ($i=0; $i<count($sumcol); $i++){
        $N=$N+$sumcol[$i];
    }
    $exp=array(array());
    $expsumcol=array();
    for ($i=0; $i<$nrow; $i++){
        for($j=0; $j<$ncol; $j++){
            $exp[$i][$j]=($sumcol[$i]*$sumrow[$j])/$N;
        }
        $expsumcol[$i]=sum($exp[$i]);
    }
    $expsumrow=array();
    for($i=0; $i<$ncol; $i++){
        $sumex=0;
        for($j=0; $j<$nrow; $j++){
            $sumex=$sumex+$exp[$j][$i];
        }
        $expsumrow[$i]=$sumex;
    }
    $chisqcal=0;
    $csval=array(array());
    for($i=0; $i<$nrow; $i++){
        for($j=0; $j<$ncol; $j++){
            $chisqcal=$chisqcal+(pow(($arr[1+$i][$j]-$exp[$i][$j]), 2))/$exp[$i][$j];
            $csval[$i][$j]=(pow(($arr[1+$i][$j]-$exp[$i][$j]), 2))/$exp[$i][$j];
        }
    }
    $csvalrow=array();
    for($i=0; $i<$ncol; $i++){
        $csrowsums=0;
        for($j=0; $j<$nrow; $j++){
            $csrowsums=$csrowsums+$csval[$j][$i];
        }
        $csvalrow[$i]=$csrowsums;
    }
    /*$cstotarr=array();
    for($i=0; $i<$ncol; $i++){
        $cstotarr[$i]=sum($csvalrow[$i]);
    }*/
    $c=sqrt($chisqcal/($chisqcal+$N));
    $t=sqrt($chisqcal/($N*sqrt(($ncol-1)*($nrow-1))));
    $text1="Karl Pearson coefficient of contingency: ".$c."";
    $text2="Tschprow's coefficient of contingency: ".$t."";
    $ndata=array($nrow, $ncol);
    $sums=array($sumcol, $sumrow);
    $expsums=array($expsumcol, $csvalrow);
    $expN=sum($csvalrow);
    //$sig=pchisq($chisqcal, $df, 0);
    //$inferential=array($df, $sl, $sig);
    // outputs arr, karl pearson text, tschprow's coefficient text, $chisqcal, column summation array, row summation array, total frequency
    return array($arr, $text1, $chisqcal, $text2, $N, $ndata, $sums, $freq, $exp, $expsums, $expN);
}


function pearsoncorr($arr){
    $a=regMeasures(1, $arr[1], $arr[2]);
    $b=coffBestFit(1, $arr[1], $arr[2]);
    $rsq=$a[1];
    $stdv=$a[0];
    $xstdv=stdev($arr[1]);
    $ystdv=stdev($arr[2]);
    $n=count($arr[1]);
    $sumx=0;
    for($i=0; $i<$n; $i++){
        $sumx=$sumx+$arr[1][$i];
    }
    $xavg=$sumx/$n;
    $sumy=0;
    for($i=0; $i<$n; $i++){
        $sumy=$sumy+$arr[2][$i];
    }
    $yavg=$sumy/$n;
    $sumcov=0;
    for($i=0; $i<$n; $i++){
        $sumcov=$sumcov+($arr[1][$i]-$xavg)*($arr[2][$i]-$yavg);
    }
    $cov=$sumcov/($n-1);
    $r=$cov/($xstdv*$ystdv);
    //outputs arr, Linear R (coefficient of correlation), linear standard deviation  
    return array($arr, $b, $rsq, $stdv, $r, $cov, $xavg, $yavg);
}


//statistical calculator-----------------------------------------------------------
function zcalculate($arr){
    if ($arr[0]=="zcv"){
        $p=$arr[1];
        return array($arr, probit($p));
    }
    else if($arr[0]=="zprob"){
        $z=$arr[1];
        return array($arr, zcdf($z));
    }
}

function tcalculate($arr){
    $prob=$arr[1][0];
    $df=$arr[1][1];
    return array($arr, tValue($prob, $df));
}

function chicalculate($arr){
    if ($arr[0]=="chicv"){
        $p=1-$arr[1][0];
        $df=$arr[1][1];
        return array($arr, qchisq($p, $df));
    }
    else if($arr[0]=="chiprob"){
        $cv=$arr[1][0];
        $df=$arr[1][1];
        return array($arr, pchisq($cv, $df, 0));
    }
}

function fcalculator($arr){
    if ($arr[0]=="fcv"){
        $p=1-$arr[1][0];
        $dfnum=$arr[1][1];
        $dfden=$arr[1][2];
        return array($arr, fValue($p, $dfnum, $dfden));
    }
    else if ($arr[0]=="fprob"){
        $f=$arr[1][0];
        $dfnum=$arr[1][1];
        $dfden=$arr[1][2];
        return array($arr, fcdf($f, $dfnum, $dfden));
    }
}

function discrete($arr){
    if ($arr[0]=="bincal"){
        $n=$arr[1][0];
        $p=$arr[1][1];
        $x=$arr[1][2];
        return array($arr, ((gamma($n+1)/(gamma(($n-$x)+1)*gamma($x+1)))*pow($p, $x)*pow((1-$p), ($n-$x))));
    }
    else if($arr[0]=="psncal"){
        $mean=$arr[1][0];
        $x=$arr[1][1];
        return array($arr, ((pow($mean, $x)*exp(-$mean))/gamma($x+1)));
    }
}

function cochran($arr){
    $countcol=count($arr)-2;
    $countrow=count($arr[2]);
    $czero=array();
    $cone=array();
    $mean=array();
    $stdvs=array();
    $N=0;
    for($i=0; $i<$countcol; $i++){
        $s=0;
        $cz=0;
        $co=0;
        for($j=0; $j<$countrow; $j++){
            //
            $s=$s+(int)$arr[$i+2][$j];
            if($arr[$i+2][$j]==0){
                $cz+=1;
            }
            else{
                $co+=1;
            }
        }
        $N=$N+$s;
        $czero[$i]=$cz;
        $cone[$i]=$co;
        //
        $mean[$i]=$cone[$i]/count($arr[2+$i]);
        $stdvs[$i]=stdev($arr[2+$i]);
    }
    $sum1=0;
    for($i=0; $i<$countcol; $i++){
        $sum=0;
        for($j=0; $j<$countrow; $j++){
            //
            $sum=$sum+(int)$arr[$i+2][$j];
        }
        $sum1=$sum1+(pow(($sum-($N/$countcol)), 2));
    }
    $sum3=0;
    for($i=0; $i<$countrow; $i++){
        $sum2=0;
        for($j=0; $j<$countcol; $j++){
            //
            $sum2=$sum2+(int)$arr[$j+2][$i];
        }
        $sum3=$sum3+($sum2*($countcol-$sum2));
    }
    if ($sum3!=0){
        $t=$countcol*($countcol-1)*($sum1/$sum3);
    }
    else{
        $t=0;
    }
    //
    $sl=$arr[1];
    $df=$countcol-1;
    $chisqtab=qchisq($sl, $df);
    if($t<=$chisqtab){
        $text="There is no significant difference in effectiveness between treatments. Alternate hypothesis is rejected";
    }
    else if($t>$chisqtab){
        $text="There is significant difference in effectiveness between treatments. Alternate hypothesis is accepted";
    }
    $sumone=sum($cone);
    $sumzero=sum($czero);
    $descriptive=array($mean, $stdvs, $sumone, $sumzero);
    $sig=pchisq($t, $df, 0);
    $inf=array($df, $sl, $sig);
    $nData=array($countcol, $countrow);
    return array($arr, $text, $t, $chisqtab, $N, $nData, $czero, $cone, $descriptive, $inf);
}

