import SelectInput from '@/Components/SelectInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { CONDITION_LABELS } from '@/Utils/inventoryStatus';
import { Head, router } from '@inertiajs/react';

function formatDate(value) {
    return new Date(value).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

export default function Report({ summary, byCategory, recentLoans, rtOptions, filters }) {
    const updateFilter = (value) => {
        router.get(route('inventory-report.index'), { rt_id: value }, { preserveState: true });
    };

    const cards = [
        { label: 'Total Jenis Barang', value: summary.total_items },
        { label: 'Total Unit', value: summary.total_quantity },
        { label: 'Sedang Dipinjam', value: summary.active_loans },
        { label: 'Terlambat Kembali', value: summary.overdue_loans },
    ];

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Laporan Inventaris
                </h2>
            }
        >
            <Head title="Laporan Inventaris" />

            {rtOptions.length > 1 && (
                <div className="mb-4">
                    <SelectInput
                        value={filters.rt_id ?? ''}
                        onChange={(e) => updateFilter(e.target.value)}
                    >
                        <option value="">Semua RT</option>
                        {rtOptions.map((rt) => (
                            <option key={rt.id} value={rt.id}>
                                RT {rt.nomor_rt}
                            </option>
                        ))}
                    </SelectInput>
                </div>
            )}

            <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                {cards.map((card) => (
                    <div
                        key={card.label}
                        className="rounded-xl border border-gray-100 bg-white p-5 shadow-sm"
                    >
                        <p className="text-sm text-gray-500">{card.label}</p>
                        <p className="mt-1 text-2xl font-semibold text-gray-900">{card.value}</p>
                    </div>
                ))}
            </div>

            <div className="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 className="text-sm font-semibold text-gray-900">Barang per Kategori</h3>
                    <table className="mt-4 min-w-full divide-y divide-gray-100 text-sm">
                        <thead className="text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                            <tr>
                                <th className="py-2">Kategori</th>
                                <th className="py-2 text-right">Jenis Barang</th>
                                <th className="py-2 text-right">Total Unit</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {byCategory.map((row) => (
                                <tr key={row.category}>
                                    <td className="py-2 text-gray-900">{row.category}</td>
                                    <td className="py-2 text-right text-gray-600">{row.total}</td>
                                    <td className="py-2 text-right text-gray-600">
                                        {row.total_quantity}
                                    </td>
                                </tr>
                            ))}
                            {byCategory.length === 0 && (
                                <tr>
                                    <td colSpan={3} className="py-4 text-center text-gray-500">
                                        Belum ada data.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 className="text-sm font-semibold text-gray-900">Kondisi Barang</h3>
                    <ul className="mt-4 space-y-2 text-sm">
                        {Object.entries(CONDITION_LABELS).map(([key, label]) => (
                            <li key={key} className="flex items-center justify-between">
                                <span className="text-gray-600">{label}</span>
                                <span className="font-medium text-gray-900">
                                    {summary.by_condition?.[key] ?? 0}
                                </span>
                            </li>
                        ))}
                    </ul>
                </div>
            </div>

            <div className="mt-6 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <div className="border-b border-gray-100 px-6 py-4">
                    <h3 className="text-sm font-semibold text-gray-900">Peminjaman Terbaru</h3>
                </div>
                <table className="min-w-full divide-y divide-gray-100 text-sm">
                    <thead className="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                        <tr>
                            <th className="px-4 py-3">Barang</th>
                            <th className="px-4 py-3">Peminjam</th>
                            <th className="px-4 py-3">Tgl Pinjam</th>
                            <th className="px-4 py-3">Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {recentLoans.map((loan) => (
                            <tr key={loan.id}>
                                <td className="px-4 py-3 font-medium text-gray-900">
                                    {loan.item?.name}
                                </td>
                                <td className="px-4 py-3 text-gray-600">{loan.borrower_name}</td>
                                <td className="px-4 py-3 text-gray-600">
                                    {formatDate(loan.loan_date)}
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {loan.handled_by?.name}
                                </td>
                            </tr>
                        ))}
                        {recentLoans.length === 0 && (
                            <tr>
                                <td colSpan={4} className="px-4 py-6 text-center text-gray-500">
                                    Belum ada peminjaman.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </AuthenticatedLayout>
    );
}
