<?php
    require('vendor/autoload.php');

    use StatisticsLibrary\Lib\MethodsLibrary;
    use StatisticsLibrary\Lib\DistributionLibrary;

    $obj = new MethodsLibrary;

    //$probit = $obj->probitZ(0.05);

    $arr=array(1, 4, 2, 8, 5);
    var_dump((new MethodsLibrary)->standardDeviation($arr));

    $student_t = (new DistributionLibrary)->studentTCriticalValue(0.90, 9);
    var_dump($student_t);

    
?>