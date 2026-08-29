import AnnouncementForm from '@/Components/AnnouncementForm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        content: '',
        publish_date: new Date().toISOString().slice(0, 10),
        expire_date: '',
        image: null,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('announcements.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Buat Pengumuman
                </h2>
            }
        >
            <Head title="Buat Pengumuman" />

            <div className="max-w-xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <AnnouncementForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    onSubmit={submit}
                    submitLabel="Publikasikan"
                />
            </div>
        </AuthenticatedLayout>
    );
}
