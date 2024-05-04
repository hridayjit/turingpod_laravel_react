<?php

    namespace App\Http\Controllers\SmartStatistics\StatisticsLibrary;
    use App\Http\Controllers\SmartStatistics\StatisticsLibrary\OperationsLibrary;

    class MethodsLibrary{
        //polynomial interpolation
        public function interpolationConstant($x, $y){//polynomial interpolation constant
            $lenX=count($x);
            $deg=$lenX-1;
            $xMatrix=array(array());
            for($i=0; $i<$lenX; $i++){
                for ($j=0; $j<=$deg; $j++){
                    $xMatrix[$i][$j]=pow($x[$i], $j);
                }
            }
            $operationsLibrary = new OperationsLibrary;
            $xTranspose=$operationsLibrary->transpose($xMatrix);
            $xMatSq=$operationsLibrary->matrixMult($xTranspose, $xMatrix);
            $xy=$operationsLibrary->matrixMult($xTranspose, $y);
            $xInv=$operationsLibrary->inversion_matrix($xMatSq);
            return $operationsLibrary->matrixMult($xInv, $xy);
        }
        
        public function polynomialInterpolation($x1, $x, $y){//polynomial interporation value, $x1 for an arbitrary value, $x and and $y are data points
            $lenX=count($x);
            $deg=$lenX-1;
            $b=$this->interpolationConstant($x, $y);
            $sum=0;
            for ($i=0; $i<=$deg; $i++){
                $sum+=$b[$i]*pow($x1, $i);
            }
            return $sum;
        }

        //polynomial regression
        private function xArray($deg, $x){
            $n=count($x);
            $xsum=array();
            for ($i=0; $i<=(2*$deg); $i++){
                $sum=0;
                for($j=0; $j<$n; $j++){
                    $sum=$sum+pow($x[$j], $i);
                }
                $xsum[$i]=$sum;
            }
            $xArr=array(array());
            for ($k=0; $k<=$deg; $k++){
                for($l=0; $l<=$deg; $l++){
                    $xArr[$k][$l]=$xsum[$l+$k];
                }
            }
            return $xArr;
        }
        
        private function yArray($deg, $x, $y){
            $n=count($y);
            $yArr=array();
            for ($i=0; $i<=$deg; $i++){
                $sum=0;
                for($j=0; $j<$n; $j++){
                    $sum=$sum+$y[$j]*pow($x[$j], $i);
                }
                $yArr[$i]=$sum;
            }
            return $yArr;
        }
        
        public function coffBestFit($deg, $x, $y){
            $X=$this->xArray($deg, $x);
            $Y=$this->yArray($deg, $x, $y);
            $operationsLibrary = new OperationsLibrary;
            $Xinv=$operationsLibrary->inversion_matrix($X);
            return $operationsLibrary->matrixMult($Xinv, $Y);
        }

        public function polynomialRegression($x1, $deg, $x, $y){//polynomial regression value main function, $x1 arbitrary x value, $x and $y are data points
            if ($deg<count($x)-1){
                $b=$this->coffBestFit($deg, $x, $y);
                $sum=0;
                for($i=0; $i<=$deg; $i++){
                    $sum=$sum+$b[$i]*pow($x1, $i);
                }
                return $sum;
            }
        }

        //exponential regression value
        public function exponentialRegression($x1, $x, $y){
            $ynew=array();
            for($i=0; $i<count($y); $i++){
                $ynew[$i]=log($y[$i]);
            }
            $yprocessed=$this->polynomialRegression($x1, 1, $x, $ynew);
            return exp($yprocessed);
        }
        //logarithmic regression value;
        public function logarithmicRegression($x1, $x, $y){
            $xnew=array();
            for ($i=0; $i<count($x); $i++){
                $xnew[$i]=log($x[$i]);
            }
            return $this->polynomialRegression($x1, 1, $xnew, $y);
        }

        public function standardDeviation($arr){
            $n=count($arr);
            $sum=0;
            for($i=0; $i<$n; $i++){
                $sum+=$arr[$i];
            }
            $avg=$sum/$n;
            $sum1=0;
            for($i=0; $i<$n; $i++){
                $sum1+=pow($arr[$i]-$avg, 2);
            }
            return sqrt($sum1/($n-1));
        }

        //r value
        public function rVal ($x, $y){
            $n=count($x);
            $xstdv=$this->standardDeviation($x);
            $ystdv=$this->standardDeviation($y);
            $sumx=0;
            for($i=0; $i<$n; $i++){
                $sumx+=$x[$i];
            }
            $xavg=$sumx/$n;
            $sumy=0;
            for($i=0; $i<$n; $i++){
                $sumy+=$y[$i];
            }
            $yavg=$sumy/$n;
            $sumcov=0;
            for($i=0; $i<$n; $i++){
                $sumcov+=($x[$i]-$xavg)*($y[$i]-$yavg);
            }
            $cov=$sumcov/($n-1);
            return $cov/($xstdv*$ystdv);
        }

        //regression measures
        public function regressionMeasures($deg, $x, $y){
            $ysum=0;
            $n=count($x);
            for($l=0; $l<count($y); $l++){
                $ysum=$ysum+$y[$l];
            }
            $yavg=$ysum/count($y);
            $b=$this->coffBestFit($deg, $x, $y);
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

        public function meanStdevError($arr){
            $n=count($arr);
            $sum=0;
            for($i=0; $i<$n; $i++){
                $sum+=$arr[$i];
            }
            $mean=$sum/$n;
            $stdv=$this->standardDeviation($arr);
            $sterror=($stdv/sqrt($n))*2;
            return array($mean, $stdv, $sterror);
        }        
    }

?>