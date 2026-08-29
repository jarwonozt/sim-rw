import FamilyHeadForm from '@/Components/FamilyHeadForm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Create({ rtOptions }) {
    const { data, setData, post, processing, errors } = useForm({
        no_kk: '',
        rt_id: rtOptions.length === 1 ? rtOptions[0].id : '',
        address: '',
        postal_code: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('family-heads.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Tambah Kepala Keluarga
                </h2>
            }
        >
            <Head title="Tambah KK" />

            <div className="max-w-xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <FamilyHeadForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    rtOptions={rtOptions}
                    processing={processing}
                    onSubmit={submit}
                    submitLabel="Simpan"
                />
            </div>
        </AuthenticatedLayout>
    );
}
