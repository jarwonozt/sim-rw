import AnnouncementForm from '@/Components/AnnouncementForm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Edit({ announcement }) {
    const { data, setData, post, processing, errors } = useForm({
        title: announcement.title,
        content: announcement.content,
        publish_date: announcement.publish_date.slice(0, 10),
        expire_date: announcement.expire_date ? announcement.expire_date.slice(0, 10) : '',
        image: null,
        _method: 'put',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('announcements.update', announcement.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Edit Pengumuman
                </h2>
            }
        >
            <Head title="Edit Pengumuman" />

            <div className="max-w-xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <AnnouncementForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    onSubmit={submit}
                    submitLabel="Simpan Perubahan"
                    existingImageUrl={announcement.image ? `/storage/${announcement.image}` : null}
                />
            </div>
        </AuthenticatedLayout>
    );
}
