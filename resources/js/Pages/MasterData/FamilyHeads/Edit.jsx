import FamilyHeadForm from '@/Components/FamilyHeadForm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Edit({ familyHead, rtOptions }) {
    const { data, setData, put, processing, errors } = useForm({
        no_kk: familyHead.no_kk,
        rt_id: familyHead.rt_id,
        address: familyHead.address,
        postal_code: familyHead.postal_code ?? '',
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('family-heads.update', familyHead.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Edit Kepala Keluarga
                </h2>
            }
        >
            <Head title="Edit KK" />

            <div className="max-w-xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <FamilyHeadForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    rtOptions={rtOptions}
                    processing={processing}
                    onSubmit={submit}
                    submitLabel="Simpan Perubahan"
                />
            </div>
        </AuthenticatedLayout>
    );
}
