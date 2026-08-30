import Pagination from '@/Components/Pagination';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import SelectInput from '@/Components/SelectInput';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { CONDITION_COLORS, CONDITION_LABELS } from '@/Utils/inventoryStatus';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ items, categoryOptions, rtOptions, filters }) {
    const [search, setSearch] = useState(filters.search ?? '');

    const updateFilter = (key, value) => {
        router.get(
            route('inventory-items.index'),
            { ...filters, [key]: value },
            { preserveState: true },
        );
    };

    const submitSearch = (e) => {
        e.preventDefault();
        updateFilter('search', search);
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Barang Inventaris
                </h2>
            }
        >
            <Head title="Barang Inventaris" />

            <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex flex-wrap gap-2">
                    <form onSubmit={submitSearch} className="flex gap-2">
                        <TextInput
                            placeholder="Cari nama/kode..."
                            className="w-56"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                        />
                    </form>
                    <SelectInput
                        value={filters.inventory_category_id ?? ''}
                        onChange={(e) => updateFilter('inventory_category_id', e.target.value)}
                    >
                        <option value="">Semua Kategori</option>
                        {categoryOptions.map((category) => (
                            <option key={category.id} value={category.id}>
                                {category.name}
                            </option>
                        ))}
                    </SelectInput>
                    {rtOptions.length > 1 && (
                        <SelectInput
                            value={filters.rt_id ?? ''}
                            onChange={(e) => updateFilter('rt_id', e.target.value)}
                        >
                            <option value="">Semua RT</option>
                            {rtOptions.map((rt) => (
                                <option key={rt.id} value={rt.id}>
                                    RT {rt.nomor_rt}
                                </option>
                            ))}
                        </SelectInput>
                    )}
                </div>

                <div className="flex gap-2">
                    <Link href={route('inventory-loans.create')}>
                        <SecondaryButton>Catat Peminjaman</SecondaryButton>
                    </Link>
                    <Link href={route('inventory-items.create')}>
                        <PrimaryButton>+ Tambah Barang</PrimaryButton>
                    </Link>
                </div>
            </div>

            <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <table className="min-w-full divide-y divide-gray-100 text-sm">
                    <thead className="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                        <tr>
                            <th className="px-4 py-3">Kode</th>
                            <th className="px-4 py-3">Nama</th>
                            <th className="px-4 py-3">Kategori</th>
                            <th className="px-4 py-3">Kepemilikan</th>
                            <th className="px-4 py-3">Stok</th>
                            <th className="px-4 py-3">Kondisi</th>
                            <th className="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {items.data.map((item) => (
                            <tr key={item.id}>
                                <td className="px-4 py-3 font-mono text-xs text-gray-600">
                                    {item.code}
                                </td>
                                <td className="px-4 py-3 font-medium text-gray-900">{item.name}</td>
                                <td className="px-4 py-3 text-gray-600">{item.category?.name}</td>
                                <td className="px-4 py-3 text-gray-600">
                                    {item.rt ? `RT ${item.rt.nomor_rt}` : 'RW Pusat'}
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {item.available_quantity} / {item.quantity}
                                </td>
                                <td className="px-4 py-3">
                                    <span
                                        className={
                                            'rounded px-2 py-0.5 text-xs font-medium ' +
                                            CONDITION_COLORS[item.condition]
                                        }
                                    >
                                        {CONDITION_LABELS[item.condition]}
                                    </span>
                                </td>
                                <td className="px-4 py-3 text-right">
                                    <Link
                                        href={route('inventory-items.edit', item.id)}
                                        className="text-sm font-medium text-emerald-700 hover:underline"
                                    >
                                        Edit
                                    </Link>
                                </td>
                            </tr>
                        ))}
                        {items.data.length === 0 && (
                            <tr>
                                <td colSpan={7} className="px-4 py-6 text-center text-gray-500">
                                    Belum ada barang inventaris.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <div className="mt-4">
                <Pagination links={items.links} />
            </div>
        </AuthenticatedLayout>
    );
}
