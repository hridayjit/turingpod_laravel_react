<?php

namespace App\Http\Controllers\SmartStatistics\StatisticsLibrary;


class OperationsLibrary{

    public function identity_matrix($n)//$n->no. of dimensions
    {
        $I = array();
        for ($i = 0; $i < $n; ++ $i) {
            for ($j = 0; $j < $n; ++ $j) {
                $I[$i][$j] = ($i == $j) ? 1 : 0;
            }
        }
        return $I;
    }

    public function inversion_matrix($A){//inverse of a square matrix
        $n = count($A);
        $I = $this->identity_matrix($n);
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

    public function transpose($A){//transpose of a square or rectangular matrix
        if(is_array($A)){
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
        else{
            return null;
        }
    }

    public function matrixMult($A, $B){//Dot product / matrix multiplication of A n B Matrix
        if(is_array($A) && is_array($B) && is_array($A[0])){//A must have both rows and columns
            $countBi=count($B);//row count
            $countAi=count($A);//row count
            $countAj=count($A[0]);//column count
        
            if ($countBi==$countAj){//no of A columns must be equal to no of B rows
               if(is_array($B[0])){//B contain columns
                    $product=array(array());
                    $countBj=count($B[0]);//column count
                    for($i=0;$i<$countAi;$i++){
                        for($k=0;$k<$countBj;$k++){
                            $prod=0;
                            for($j=0;$j<$countAj;$j++){
                                $prod+=$A[$i][$j]*$B[$j][$k];
                            }
                            $product[$i][$k]=$prod;
                        }
                    }
                    return $product;
               }
               else{//B does not contain columns
                    $product=array();
                    for($i=0;$i<$countAi;$i++){
                        $prod=0;
                        for($j=0;$j<$countAj;$j++){
                            $prod+=$A[$i][$j]*$B[$j];
                        }
                        $product[$i]=$prod;
                    }
                    return $product;
                }
            }
            else{
                return null;
            }
        }
        else{
            return null;
        }
    }

    public function convertToRank($a){//ranking the 1-D array in ASC
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

    public function sum($array){//summation
        $n=count($array);
        $sum=0;
        for($i=0; $i<$n; $i++){
            $sum+=$array[$i];
        }
        return $sum;
    }
    
    public function sorting($rank){//sorting the 1-D Array
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
    
    public function repeatcount($rank){
        $u=array();
        $cnt=array();
        for($i=0; $i<count($rank); $i++){
            $c=0;
            for($j=0; $j<count($rank); $j++){
                if($i!=0){
                    if($rank[$i]!=$rank[$i-1] && $rank[$i]==$rank[$j]){
                        $c+=1;
                        if($c==2){
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
                        if($c==2){
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

    public function histogram($array){
        $sarray=$this->sorting($array);
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

    public function boxplot($arr){
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
}