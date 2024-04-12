
import DangerButton from "@/Components/DangerButton";
import SuccessButton from "@/Components/SuccessButton";
import IteratingRow from "@/Components/IteratingRow";
import { useEffect } from "react";
import { Head, useForm } from '@inertiajs/react';
import { useState } from "react";

export default function StatHypoOneView(props) {
    // let numData = 1;
    // let dataArr = [];
    // const [numData, setNumData] = useState(1);
    const [dataList, setDataList] = useState([{inputName: 'dataInput1', inputId: 'dataInput1', inputValue: '', value: 1, visibleSuccess: true, visibleDanger: false}]);

    // let dataList = [];

    useEffect((e) => {
    }, [dataList]);

    // function renderDataInput() {
    //     setDataList(
    //         (dataList, index) => [...dataList, <IteratingRow name={inputName} id={inputId} visibleSuccess={visibleSuccess} visibleDanger={visibleDanger} handleSuccessClick={handleSuccessClick} handleDangerClick={handleDangerClick} />]
    //     );
    // }

    function handleSuccessClick(e) {
        console.log(e.currentTarget.value, dataList.length);
        if((dataList.length == e.currentTarget.value) || (dataList.length < e.currentTarget.value)) {
            dataList.forEach(element => {
                element.visibleDanger = true;
                element.visibleSuccess = false;
            });
            let incrementalNum = Math.max(dataList.length, e.currentTarget.value);
            let list = [...dataList, {inputName: 'dataInput'+(incrementalNum+1), inputId: 'dataInput'+(incrementalNum+1), inputValue: '', value: (incrementalNum+1), visibleSuccess: true, visibleDanger: false}];
            setDataList(list);
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
            console.log(listArr);
            for(let i=0; i<listArr.length; i++) {
                listArr[i].inputValue = inputValueArr[i];
            }
            setDataList(listArr);
        }
    }

    function getInputValue(e) {
        console.log(document.getElementById(e.currentTarget.id).value);
        dataList.forEach(element => {
            if(element.inputId == e.currentTarget.id) {
                element.inputValue = document.getElementById(e.currentTarget.id).value;
            }
        });
        let list = [...dataList];
        setDataList(list);
        console.log(list)
    }


    return (
        <div className="card">
            <div className="card-header text-center">
                <h4>Hypothesis Test (One Sample)</h4>
            </div>
        
            <div className="card-body">
                <div className="row">
                    <div className="col-md-6 col-lg-6">
                        {/* <input type="hidden" id="currentNumData" value={numData} /> */}
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
                    <div className="col-md-6 col-lg-6">
                        <table className="table table-bordered">
                            <tbody>
                                <tr><td><b>Type of Test: </b></td><td>T-Test</td></tr>
                                <tr><td>Actual Population Mean: </td><td><input type="text" placeholder="Enter Here"/></td></tr>
                                <tr><td>Acuracy (%): </td><td><input type="number" placeholder="Enter Here"/></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
        </div>
    );
}  