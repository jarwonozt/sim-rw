import InventoryItemForm from '@/Components/InventoryItemForm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Create({ categoryOptions, rtOptions, isKetuaRt }) {
    const { data, setData, post, processing, errors } = useForm({
        inventory_category_id: '',
        rt_id: isKetuaRt ? (rtOptions[0]?.id ?? '') : '',
        name: '',
        quantity: 1,
        condition: 'baik',
        location: '',
        notes: '',
        photo: null,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('inventory-items.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Tambah Barang Inventaris
                </h2>
            }
        >
            <Head title="Tambah Barang" />

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
                    submitLabel="Simpan"
                />
            </div>
        </AuthenticatedLayout>
    );
}
