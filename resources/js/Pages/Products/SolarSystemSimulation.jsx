import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Content from '../Content';
import { Head } from '@inertiajs/react';

export default function SolarSystemSimulation ({auth}) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Solar System Simulation</h2>}
        >
            <Head title="Solar System Simulation" />
            <div className="p-2">
                
            </div>

        </AuthenticatedLayout>
        
    );
}