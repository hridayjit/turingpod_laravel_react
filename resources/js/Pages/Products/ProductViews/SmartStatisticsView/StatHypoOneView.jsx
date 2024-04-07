
import Button from "@/Components/Button";
export default function StatHypoOneView({props}) {
    let numData = 10;

    return (
        <div className="card">
            <div className="card-header text-center">
                <h4>Hypothesis Test (One Sample)</h4>
            </div>
        
            <div className="card-body">
                <div className="row">
                    <div className="col-md-6 col-lg-6">
                        <table className="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Sample Data</th>
                                    <th>Add / Remove</th>
                                </tr>
                            </thead>
                            <tbody>

                                <tr>
                                    <td><input type="number" placeholder="Enter"/></td>
                                    <td><Button color="green" ><b>Add</b></Button><Button color="red" ><b>Remove</b></Button></td>
                                </tr>
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