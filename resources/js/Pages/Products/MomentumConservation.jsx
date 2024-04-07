import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Content from '../Content';
import { Head } from '@inertiajs/react';

export default function MomentumConservation ({auth}) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Momentum Conservation</h2>}
        >
            <Head title="Momentum Conservation" />
            <div className="p-2">
                
            </div>

        </AuthenticatedLayout>
        
    );
}