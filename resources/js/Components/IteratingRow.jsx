import DangerButton from "./DangerButton";
import SuccessButton from "./SuccessButton";

export default function IteratingRow(props) {

    

    return (
        <tr>
            <td><input type="number" name={props.name} id={props.id} value={props.inputValue} onChange={props.getInputValue} placeholder="Enter"/></td>
            <td>
                {(props.visibleSuccess) && (
                    <SuccessButton onclick={props.handleSuccessClick} value={props.value}><b>Add</b></SuccessButton>
                )}
                {(props.visibleDanger) && (
                    <DangerButton onclick={props.handleDangerClick} value={props.value}><b>Remove</b></DangerButton>
                )}
                
            </td>
        </tr>
    );
}