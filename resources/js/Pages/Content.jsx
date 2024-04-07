import { React } from 'react';
import Button from '@/Components/Button'; 

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