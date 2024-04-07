import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Content from '../Content';
import { Head } from '@inertiajs/react';

export default function QueryRecommend ({auth}) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Query and Recommend ...</h2>}
        >
            <Head title="Query and Recommend" />
            <div className="p-2">
                
            </div>

        </AuthenticatedLayout>
        
    );
}