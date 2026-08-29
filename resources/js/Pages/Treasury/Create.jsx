import TreasuryForm from '@/Components/TreasuryForm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Create({ categories }) {
    const { data, setData, post, processing, errors } = useForm({
        treasury_category_id: '',
        amount: '',
        transaction_date: new Date().toISOString().slice(0, 10),
        description: '',
        proof_photo: null,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('treasuries.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Catat Transaksi Kas
                </h2>
            }
        >
            <Head title="Catat Transaksi Kas" />

            <div className="max-w-xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <TreasuryForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    categories={categories}
                    processing={processing}
                    onSubmit={submit}
                    submitLabel="Simpan Transaksi"
                />
            </div>
        </AuthenticatedLayout>
    );
}
