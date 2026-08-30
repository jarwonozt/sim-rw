import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';

function valuesFromCategory(category) {
    return {
        name: category?.name ?? '',
    };
}

function CategoryFormModal({ show, onClose, category }) {
    const isEditing = Boolean(category);

    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm(
        valuesFromCategory(category),
    );

    // Modal ini tetap ter-mount di belakang layar (cuma `show` yang berubah),
    // jadi perlu di-resync manual tiap kali kategori yang diedit berganti.
    useEffect(() => {
        setData(valuesFromCategory(category));
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [category]);

    const close = () => {
        reset();
        clearErrors();
        onClose();
    };

    const submit = (e) => {
        e.preventDefault();
        const options = { onSuccess: () => close() };

        if (isEditing) {
            put(route('inventory-categories.update', category.id), options);
        } else {
            post(route('inventory-categories.store'), options);
        }
    };

    return (
        <Modal show={show} onClose={close} maxWidth="md">
            <form onSubmit={submit} className="p-6">
                <h2 className="text-lg font-semibold text-gray-900">
                    {isEditing ? 'Edit Kategori' : 'Tambah Kategori'}
                </h2>

                <div className="mt-4">
                    <InputLabel htmlFor="name" value="Nama Kategori" />
                    <TextInput
                        id="name"
                        className="mt-1 block w-full"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        isFocused
                    />
                    <InputError message={errors.name} className="mt-1" />
                </div>

                <div className="mt-6 flex justify-end gap-3">
                    <SecondaryButton onClick={close}>Batal</SecondaryButton>
                    <PrimaryButton disabled={processing}>Simpan</PrimaryButton>
                </div>
            </form>
        </Modal>
    );
}

export default function Index({ categories }) {
    const [formState, setFormState] = useState({ show: false, category: null });
    const [confirmDelete, setConfirmDelete] = useState(null);

    const destroy = () => {
        router.delete(route('inventory-categories.destroy', confirmDelete.id), {
            onFinish: () => setConfirmDelete(null),
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Kategori Inventaris
                </h2>
            }
        >
            <Head title="Kategori Inventaris" />

            <div className="mb-4 flex justify-end">
                <PrimaryButton onClick={() => setFormState({ show: true, category: null })}>
                    + Tambah Kategori
                </PrimaryButton>
            </div>

            <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <table className="min-w-full divide-y divide-gray-100 text-sm">
                    <thead className="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                        <tr>
                            <th className="px-4 py-3">Nama</th>
                            <th className="px-4 py-3">Jenis Barang</th>
                            <th className="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {categories.map((category) => (
                            <tr key={category.id}>
                                <td className="px-4 py-3 font-medium text-gray-900">
                                    {category.name}
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {category.items_count} barang
                                </td>
                                <td className="px-4 py-3 text-right">
                                    <button
                                        onClick={() => setFormState({ show: true, category })}
                                        className="mr-3 text-sm font-medium text-emerald-700 hover:underline"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        onClick={() => setConfirmDelete(category)}
                                        className="text-sm font-medium text-rose-600 hover:underline"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        ))}
                        {categories.length === 0 && (
                            <tr>
                                <td colSpan={3} className="px-4 py-6 text-center text-gray-500">
                                    Belum ada kategori.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <CategoryFormModal
                show={formState.show}
                category={formState.category}
                onClose={() => setFormState({ show: false, category: null })}
            />

            <Modal show={Boolean(confirmDelete)} onClose={() => setConfirmDelete(null)} maxWidth="md">
                <div className="p-6">
                    <h2 className="text-lg font-semibold text-gray-900">Hapus kategori?</h2>
                    <p className="mt-2 text-sm text-gray-600">
                        Kategori "{confirmDelete?.name}" akan dihapus permanen.
                    </p>
                    <div className="mt-6 flex justify-end gap-3">
                        <SecondaryButton onClick={() => setConfirmDelete(null)}>
                            Batal
                        </SecondaryButton>
                        <DangerButton onClick={destroy}>Hapus</DangerButton>
                    </div>
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}
