import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Content from '../Content';
import { Head } from '@inertiajs/react';

export default function ChatApp ({auth}) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Chat App</h2>}
        >
            <Head title="Chat App" />
            <div className="p-2">
                
            </div>

        </AuthenticatedLayout>
        
    );
}