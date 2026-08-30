import Pagination from '@/Components/Pagination';
import PrimaryButton from '@/Components/PrimaryButton';
import SelectInput from '@/Components/SelectInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { LOAN_STATUS_COLORS, LOAN_STATUS_LABELS } from '@/Utils/inventoryStatus';
import { Head, Link, router } from '@inertiajs/react';

function formatDate(value) {
    return new Date(value).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

export default function Index({ loans, filters }) {
    const updateFilter = (key, value) => {
        router.get(
            route('inventory-loans.index'),
            { ...filters, [key]: value },
            { preserveState: true },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Peminjaman Barang
                </h2>
            }
        >
            <Head title="Peminjaman Barang" />

            <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <SelectInput
                    value={filters.status ?? ''}
                    onChange={(e) => updateFilter('status', e.target.value)}
                >
                    <option value="">Semua Status</option>
                    <option value="dipinjam">Dipinjam</option>
                    <option value="dikembalikan">Dikembalikan</option>
                    <option value="hilang">Hilang</option>
                </SelectInput>

                <Link href={route('inventory-loans.create')}>
                    <PrimaryButton>+ Catat Peminjaman</PrimaryButton>
                </Link>
            </div>

            <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <table className="min-w-full divide-y divide-gray-100 text-sm">
                    <thead className="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                        <tr>
                            <th className="px-4 py-3">Barang</th>
                            <th className="px-4 py-3">Peminjam</th>
                            <th className="px-4 py-3">Jumlah</th>
                            <th className="px-4 py-3">Tgl Pinjam</th>
                            <th className="px-4 py-3">Jatuh Tempo</th>
                            <th className="px-4 py-3">Status</th>
                            <th className="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {loans.data.map((loan) => (
                            <tr key={loan.id}>
                                <td className="px-4 py-3 font-medium text-gray-900">
                                    {loan.item?.name}
                                </td>
                                <td className="px-4 py-3 text-gray-600">{loan.borrower_name}</td>
                                <td className="px-4 py-3 text-gray-600">
                                    {loan.quantity_borrowed}
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {formatDate(loan.loan_date)}
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {formatDate(loan.due_date)}
                                </td>
                                <td className="px-4 py-3">
                                    <span
                                        className={
                                            'rounded px-2 py-0.5 text-xs font-medium ' +
                                            (loan.is_overdue
                                                ? 'bg-rose-50 text-rose-700'
                                                : LOAN_STATUS_COLORS[loan.status])
                                        }
                                    >
                                        {loan.is_overdue
                                            ? 'Terlambat'
                                            : LOAN_STATUS_LABELS[loan.status]}
                                    </span>
                                </td>
                                <td className="px-4 py-3 text-right">
                                    <Link
                                        href={route('inventory-loans.show', loan.id)}
                                        className="text-sm font-medium text-emerald-700 hover:underline"
                                    >
                                        Detail
                                    </Link>
                                </td>
                            </tr>
                        ))}
                        {loans.data.length === 0 && (
                            <tr>
                                <td colSpan={7} className="px-4 py-6 text-center text-gray-500">
                                    Belum ada peminjaman.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <div className="mt-4">
                <Pagination links={loans.links} />
            </div>
        </AuthenticatedLayout>
    );
}
