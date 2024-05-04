import { React } from 'react';
import DangerButton from '@/Components/DangerButton'; 

export default function Content(props) {
    return (
        <div>
            <h1>
                Hello Guyz and Respected {props.data.email}
            </h1>
            <Button className='' disabled={false} children={'Button'|| 'svdf'}></Button>
        </div>
        
    );
}