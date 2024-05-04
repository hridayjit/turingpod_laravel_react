
import DangerButton from "@/Components/DangerButton";
import SuccessButton from "@/Components/SuccessButton";
import PrimaryButton from "@/Components/PrimaryButton";
import ResetButton from "@/Components/ResetButton";
import IteratingRow from "@/Components/IteratingRow";
import { useRef, useEffect } from "react";
import { Head, useForm } from '@inertiajs/react';
import { useState } from "react";
import axios from "axios";

export default function StatHypoOneView(props) {
    const tbodyRef = useRef(null);
    //maximize_minimize
    const [isMaximized, setIsMaximized] = useState(false);
    const [isMinimized, setIsMinimized] = useState(true);
    //inputs
    const [dataList, setDataList] = useState([{inputName: 'dataInput1', inputId: 'dataInput1', inputValue: '', value: 1, visibleSuccess: true, visibleDanger: false}]);
    const [testTypeName, setTestTypeName] = useState('T-Test');
    const [showPreview, setShowPreview] = useState(false);
    const[popMean, setPopMean] = useState('');
    const [accuracy, setAccuracy] = useState('95');
    const [testDirection, setTestDirection] = useState('2');
    const [showStDev, setShowStDev] = useState(false);
    const [popStDev, setPopStDev] = useState('');
    const [defineClass, setDefineClass] = useState('col-md-12 col-lg-6 col-sm-12 col-xs-12');
    //outputs
    const [hypothesis, setHypothesis] = useState('');
    const [tCalculated, setTCalculated] = useState('');
    const [tTabular, setTTabular] = useState('');
    const [dataMean, setDataMean] = useState('');
    const [tStandardDeviation, setTStandardDeviation] = useState('');
    const [tCalCritical, setTCalCritical] = useState('');
    const [tTabCritical, setTTabCritical] = useState('');
    const [tPdf, setTPdf] = useState([]);
    const [tCdf, setTCdf] = useState([]);

    useEffect(() => {
        build();
    }, [hypothesis, tCalculated, tTabular, dataMean, tStandardDeviation, tCalCritical, tTabCritical, tPdf, tCdf]);

    useEffect(() => {
        // getResult();
    }, [popMean, dataList, accuracy, testDirection, popStDev]);

    useEffect(() => {
        if(showPreview) {
            setDefineClass('col-md-12 col-lg-4 col-sm-12 col-xs-12');
        }
        else{
            setDefineClass('col-md-12 col-lg-6 col-sm-12 col-xs-12');
        }
    }, [showPreview]);

    useEffect(() => {
        // Adjust tbody height after data changes
        if (tbodyRef.current) {
          tbodyRef.current.style.height = "200px"; // Set your desired height here
        }
      }, [dataList]);

    // function renderDataInput() {
    //     setDataList(
    //         (dataList, index) => [...dataList, <IteratingRow name={inputName} id={inputId} visibleSuccess={visibleSuccess} visibleDanger={visibleDanger} handleSuccessClick={handleSuccessClick} handleDangerClick={handleDangerClick} />]
    //     );
    // }

    function handleSuccessClick(e) {
        if((dataList.length == e.currentTarget.value) || (dataList.length < e.currentTarget.value)) {
            dataList.forEach(element => {
                element.visibleDanger = true;
                element.visibleSuccess = false;
            });
            let incrementalNum = Math.max(dataList.length, e.currentTarget.value);
            let list = [...dataList, {inputName: 'dataInput'+(incrementalNum+1), inputId: 'dataInput'+(incrementalNum+1), inputValue: '', value: (incrementalNum+1), visibleSuccess: true, visibleDanger: false}];
            setDataList(list);
            if(list.length >= 30) {
                setTestTypeName('Z-Test');
                if(!showStDev) {
                    setShowStDev(true);
                }
            }
            else{
                setTestTypeName('T-Test');
                if(showStDev) {
                    setShowStDev(false);
                    setPopStDev('');
                }
            }
        }
    }

    function handleDangerClick(e) {
        //decrement numData and Row
        if(dataList.length > 1) {
            let inputValueArr = [];
            dataList.forEach(element => {
                if(element.value != e.currentTarget.value) {
                    inputValueArr.push(element.inputValue);
                }
            });
            let list = [...dataList];
            const listArr = list.filter((obj) => {
                return obj.value != e.currentTarget.value;
            });
            for(let i=0; i<listArr.length; i++) {
                listArr[i].inputValue = inputValueArr[i];
            }
            setDataList(listArr);
            if(listArr.length >= 30) {
                setTestTypeName('Z-Test');
                if(!showStDev) {
                    setShowStDev(true);
                }
            }
            else{
                setTestTypeName('T-Test');
                if(showStDev) {
                    setShowStDev(false);
                    setPopStDev('');
                }
            }
            // getResult();
        }
    }

    function getInputValue(e) {
        dataList.forEach(element => {
            if(element.inputId == e.currentTarget.id) {
                element.inputValue = document.getElementById(e.currentTarget.id).value;
            }
        });
        let list = [...dataList];
        setDataList(list);
        // getResult();
    }

    function getPopMean(e) {
        setPopMean(e.currentTarget.value);
        
    }

    function getAccuracy(e) {
        setAccuracy(e.currentTarget.value);
        // getResult();
    }

    function getTestDirection(e) {
        setTestDirection(e.currentTarget.value);
        // getResult();
    }

    function getPopStDev(e) {
        setPopStDev(e.currentTarget.value);
    }

    async function getResult() {
        let list = [];
        dataList.forEach(element => {
            if(element.inputValue != '') {
                list.push(element.inputValue);
            }
        });
        let allow = false;
        let postData = {};
        if(list.length != 0 && list.length >= 30) {
            if(list.length > 2) {
                if(popMean != '' && accuracy != '' && testDirection != '' && popStDev != '') {
                    postData = {popMean:popMean, accuracy:accuracy, testDirection:testDirection, data:list, popStDev:popStDev};
                    allow = true;
                }
            }
            else{
                alert("The no. of data must be atleast 3 or more");
            }
        }
        else if(list.length != 0 && list.length < 30) {
            if(list.length > 2) {
                if(popMean != '' && accuracy != '' && testDirection != '') {
                    postData = {popMean:popMean, accuracy:accuracy, testDirection:testDirection, data:list};
                    allow = true;
                }
            }
            else {
                alert("The no. of data must be atleast 3 or more");
            }
        }
        if(allow) {
            // console.log(postData);
            try {
                const response = await axios.post(route('getHypoOne'), {data: postData});
                if(response.status == 200 && response.statusText == 'OK') {
                    setShowPreview(true);
                    let data = response.data;
                    if(data.test_type == 't') {
                        setHypothesis(data.hypothesis);
                        setDataMean(data.data_mean);
                        setTStandardDeviation(data.standard_deviation);
                        setTCalCritical(data.t_cal_critical);
                        setTCalculated(data.t_calculated);
                        setTTabCritical(data.t_tab_critical);
                        setTTabular(data.t_tabular);
                        setTPdf(data.t_prob_dist_function);
                        setTCdf(data.t_cum_dist_function);
                    }
                    else if(data.test_type == 'z') {

                    }
                }
            } catch (error) {
                console.log(error);
            }
        }
    }

    function resetClick(e) {
        let list = [{inputName: 'dataInput1', inputId: 'dataInput1', inputValue: '', value: 1, visibleSuccess: true, visibleDanger: false}];
        setDataList(list);
        setShowPreview(false);
        setPopMean('');
        setAccuracy('95');
        setTestDirection('2');
        setPopStDev('');
        setShowStDev(false);
        setTestTypeName('T-Test');
    }

    function submitData(e) {
        // console.log(e.target.value);
        getResult();
    }

    function toggleMaxMin() {
        if(isMaximized && !isMinimized) {
            setIsMaximized(false);
            setIsMinimized(true);
        }
        else if(!isMaximized && isMinimized) {
            setIsMaximized(true);
            setIsMinimized(false);
        }
    }

    function build() {
        console.log(hypothesis, tCalculated, tTabular, dataMean, tStandardDeviation, tCalCritical, tTabCritical, tPdf, tCdf);

    }

    return (
        <div className="card">
            <div className="card-header text-center">
                <h4>Hypothesis Test (One Sample)</h4>
            </div>
        
            <div className="card-body">
                
                <div className="row">
                    <div className={defineClass}>
                        {/* <input type="hidden" id="currentNumData" value={numData} /> */}
                        <div style={{overflowY: "scroll", height: "300px"}}>
                            <table className="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Sample Data</th>
                                        <th>Add / Remove</th>
                                    </tr>
                                </thead>
                                    <tbody>
                                        {dataList.map((keys, index) => {
                                            return (
                                                <IteratingRow key={index} value={keys.value} name={keys.inputName} id={keys.inputId} inputValue={keys.inputValue} getInputValue={getInputValue} visibleSuccess={keys.visibleSuccess} visibleDanger={keys.visibleDanger} handleSuccessClick={handleSuccessClick} handleDangerClick={handleDangerClick} />
                                            )
                                        })}
                                    </tbody>
                            </table>
                        </div>
                    </div>
                    <div className={defineClass}>
                        <table className="table table-bordered">
                            <tbody>
                                <tr><td><b>Type of Test: </b></td><td>{testTypeName}</td></tr>
                                <tr><td>Sample Population Mean: </td><td><input type="number" value={popMean} onChange={getPopMean} placeholder="Enter Here"/></td></tr>
                                {(showStDev) && (
                                    <tr><td>Sample Population Standard Deviation: </td><td><input type="number" value={popStDev} onChange={getPopStDev} placeholder="Enter Here" disabled={!showStDev}/></td></tr>
                                )}
                                <tr><td>Acuracy (%): </td><td><input type="number" value={accuracy} onChange={getAccuracy} placeholder="Enter Here"/></td></tr>
                                <tr>
                                    <td>Direction of test: </td>
                                    <td>
                                        <select name="testdirection" id="testdirection" value={testDirection} onChange={getTestDirection}>
                                            <option value="1">One Tail (Single Directional)</option>
                                            <option value="2">Two Tail (Bi-Directional)</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    {(showPreview) && (
                        <div className={defineClass}>
                            <div className={`window ${isMaximized ? 'maximized' : ''}${isMinimized ? 'minimized' : ''}`}>
                                <div><button onClick={toggleMaxMin}>{(isMaximized && !isMinimized) ? '□' : '_'}</button></div>
                            </div>
                        </div>
                    )}
                    
                    
                </div>
                
            </div>
            <div className="card-footer">
                <ResetButton onclick={resetClick}>Reset</ResetButton><PrimaryButton onclick={submitData}>Submit</PrimaryButton>
            </div>
        </div>
    );
}  