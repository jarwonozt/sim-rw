import DangerButton from '@/Components/DangerButton';
import Modal from '@/Components/Modal';
import SecondaryButton from '@/Components/SecondaryButton';
import TreasuryForm from '@/Components/TreasuryForm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function Edit({ treasury, categories }) {
    const [confirmDelete, setConfirmDelete] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        treasury_category_id: treasury.treasury_category_id,
        amount: parseFloat(treasury.amount),
        transaction_date: treasury.transaction_date.slice(0, 10),
        description: treasury.description,
        proof_photo: null,
        _method: 'put',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('treasuries.update', treasury.id));
    };

    const destroy = () => {
        router.delete(route('treasuries.destroy', treasury.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Edit Transaksi Kas
                </h2>
            }
        >
            <Head title="Edit Transaksi Kas" />

            <div className="max-w-xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <TreasuryForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    categories={categories}
                    processing={processing}
                    onSubmit={submit}
                    submitLabel="Simpan Perubahan"
                    existingPhotoUrl={`/storage/${treasury.proof_photo}`}
                />

                <div className="mt-6 border-t border-gray-100 pt-4">
                    <button
                        type="button"
                        onClick={() => setConfirmDelete(true)}
                        className="text-sm font-medium text-rose-600 hover:underline"
                    >
                        Hapus transaksi ini
                    </button>
                </div>
            </div>

            <Modal show={confirmDelete} onClose={() => setConfirmDelete(false)} maxWidth="md">
                <div className="p-6">
                    <h2 className="text-lg font-semibold text-gray-900">
                        Hapus transaksi kas?
                    </h2>
                    <p className="mt-2 text-sm text-gray-600">
                        Tindakan ini tidak bisa dibatalkan dan akan memengaruhi saldo kas.
                    </p>
                    <div className="mt-6 flex justify-end gap-3">
                        <SecondaryButton onClick={() => setConfirmDelete(false)}>
                            Batal
                        </SecondaryButton>
                        <DangerButton onClick={destroy}>Hapus</DangerButton>
                    </div>
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}
