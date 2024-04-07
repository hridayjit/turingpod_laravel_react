import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Content from '../Content';
import { Head } from '@inertiajs/react';

export default function TensilePropertyEvaluator ({auth}) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Tensile Property Evaluator</h2>}
        >
            <Head title="Tensile Property Evaluator" />
            <div className="p-2">
                
            </div>

        </AuthenticatedLayout>
        
    );
}