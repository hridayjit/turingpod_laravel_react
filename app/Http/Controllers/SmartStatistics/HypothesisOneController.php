<?php

namespace App\Http\Controllers\SmartStatistics;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Http\Controllers\SmartStatistics\StatisticsLibrary\HypothesisLibrary;
use Illuminate\Http\Request;

class HypothesisOneController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function getResult(Request $request) {
        $req = $request->data;
        $popMean = $req['popMean'];
        $accuracy = $req['accuracy'];
        $testDirection = $req['testDirection'];
        $data = $req['data'];
        $levelOfSinificance = (1-($accuracy/100));

        if(count($data) > 0 && count($data) >= 30) {
            $testType = 'zd';
            $stDev = $req['popStDev'];
            $sendArr = [$testType, $data, [$testDirection, $popMean, $stDev, $levelOfSinificance]];
        }
        else if(count($data) > 0 && count($data) <30) {
            $testType = 'td';
            $sendArr = [$testType, $data, [$testDirection, $popMean, $levelOfSinificance]];
        }
        else{
            return;
        }
        
        $hypothesisLibrary = new HypothesisLibrary;
        $responseData = $hypothesisLibrary->hypoOneSample($sendArr);
        echo json_encode($responseData);
        // echo '<pre>';
        // var_dump($responseData);
        // die();
    }
}
