import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Content from '../Content';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import PrimaryButton from '@/Components/PrimaryButton';
import StatCalcView from './ProductViews/SmartStatisticsView/StatCalcView';
import StatPolyInterpolView from './ProductViews/SmartStatisticsView/StatPolyInterpolView';
import StatRegressionView from './ProductViews/SmartStatisticsView/StatRegressionView';
import StatBynoPoissonView from './ProductViews/SmartStatisticsView/StatBynoPoissonView';
import StatHypoTwoView from './ProductViews/SmartStatisticsView/StatHypoTwoView';
import StatHypoOneView from './ProductViews/SmartStatisticsView/StatHypoOneView';
import { useEffect } from 'react';

export default function SmartStatistics ({auth}) {
    const { data, setData, post, processing, errors, reset } = useForm({
        typeofoperation: "1",
        showCalc: false,
        showPolyInterpol: false,
        showRegression: false,
        showBynoPoisson: false,
        showHypoTwo: false,
        showHypoOne: false
        // typename: ""
    });

    useEffect(() => {
        getFunctions({value: data.typeofoperation});
    }, []);

    // const submit = (e) => {
    //     e.preventDefault();
    //     console.log("jhgjh");
    //     const form = e.currentTarget.value;
    //     console.log(form);
        

    //     // post(route('login'));
    // };

    // async function fetchData (postData) {
    //     try {
    //       const response = await axios.post(route('statistics'), {data: postData});
    //       console.log(response);
    //     } catch (error) {
    //       console.log(error);
    //     }
    // }

    function getFunctions(el) {
        setData({typeofoperation: el.value});
        let typeofoperation = el.value;
        
        if(typeofoperation == "6") {
            setData({showCalc: true});
        }
        else if(typeofoperation == "5") {
            setData({showPolyInterpol: true});
        }
        else if(typeofoperation == "4") {
            setData({showRegression: true});
        }
        else if(typeofoperation == "3") {
            setData({showBynoPoisson: true});
        }
        else if(typeofoperation == "2") {
            setData({showHypoTwo: true});
        }
        else if(typeofoperation == "1") {
            setData({showHypoOne: true});
        }
    }

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Smart Statistics - A Research Tool for data visualization and analysis</h2>}
        >
            <Head title="Smart Statistics" />
            <div className="p-2">
                {/* <form onSubmit={submit}> */}
                    <div>
                        <div>
                            <h3>Choose the type of statistical operation</h3>
                            <select name="typeofoperation" defaultValue={data.typeofoperation} onChange={(e) => { getFunctions(e.currentTarget) }}>
                                <option value="1">Hypothesis Test (One Sample)</option>
                                <option value="2">Hypothesis / Association Test (Two or More Sample)</option>
                                <option value="3">Hypothesis Test (Binomial / Poisson)</option>
                                <option value="4">Regression Analysis / Curve Fitting</option>
                                <option value="5">Polynomial Interpolation</option>
                                <option value="6">Statistical Calculation</option>
                            </select>
                            {/* <input type="text" name="typename" value={data.typeofoperation} readOnly /> */}
                        </div>
                    </div>
                {/* </form> */}
            </div>
            {(data.showCalc) && (
                <div className="p-2" >
                    <StatCalcView />
                </div>
            )}

            {(data.showPolyInterpol) && (
                <div className="p-2">
                    <StatPolyInterpolView />
                </div>
            )}

            {(data.showRegression) && (
                <div className="p-2">
                    <StatRegressionView />
                </div>
            )}

            {(data.showBynoPoisson) && (
                <div className="p-2">
                    <StatBynoPoissonView />
                </div>
            )}

            {(data.showHypoTwo) && (
                <div className="p-2">
                    <StatHypoTwoView />
                </div>
            )}

            {(data.showHypoOne) && (
                <div className="p-2">
                    <StatHypoOneView />
                </div>
            )}
            
        </AuthenticatedLayout>
        
    );
}

// setData({typeofoperation: e.currentTarget.value})