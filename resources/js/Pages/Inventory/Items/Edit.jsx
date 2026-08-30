import DangerButton from '@/Components/DangerButton';
import InventoryItemForm from '@/Components/InventoryItemForm';
import Modal from '@/Components/Modal';
import SecondaryButton from '@/Components/SecondaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function Edit({ item, categoryOptions, rtOptions, isKetuaRt }) {
    const [confirmDelete, setConfirmDelete] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        inventory_category_id: item.inventory_category_id,
        rt_id: item.rt_id ?? (isKetuaRt ? (rtOptions[0]?.id ?? '') : ''),
        name: item.name,
        quantity: item.quantity,
        condition: item.condition,
        location: item.location ?? '',
        notes: item.notes ?? '',
        photo: null,
        _method: 'put',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('inventory-items.update', item.id));
    };

    const destroy = () => {
        router.delete(route('inventory-items.destroy', item.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Edit Barang Inventaris
                </h2>
            }
        >
            <Head title="Edit Barang" />

            <div className="max-w-2xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <InventoryItemForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    categoryOptions={categoryOptions}
                    rtOptions={rtOptions}
                    isKetuaRt={isKetuaRt}
                    processing={processing}
                    onSubmit={submit}
                    submitLabel="Simpan Perubahan"
                    existingPhotoUrl={item.photo ? `/storage/${item.photo}` : null}
                />

                <div className="mt-6 border-t border-gray-100 pt-4">
                    <button
                        type="button"
                        onClick={() => setConfirmDelete(true)}
                        className="text-sm font-medium text-rose-600 hover:underline"
                    >
                        Hapus barang ini
                    </button>
                </div>
            </div>

            <Modal show={confirmDelete} onClose={() => setConfirmDelete(false)} maxWidth="md">
                <div className="p-6">
                    <h2 className="text-lg font-semibold text-gray-900">Hapus barang inventaris?</h2>
                    <p className="mt-2 text-sm text-gray-600">
                        Tindakan ini tidak bisa dibatalkan. Barang dengan riwayat peminjaman tidak
                        bisa dihapus.
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
