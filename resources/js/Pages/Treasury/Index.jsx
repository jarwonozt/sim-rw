import Pagination from '@/Components/Pagination';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import SelectInput from '@/Components/SelectInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

const MONTHS = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);
}

function formatDate(value) {
    return new Date(value).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

export default function Index({ treasuries, filters }) {
    const currentYear = new Date().getFullYear();
    const years = Array.from({ length: 6 }, (_, i) => currentYear - i);

    const updateFilter = (key, value) => {
        router.get(
            route('treasuries.index'),
            { ...filters, [key]: value },
            { preserveState: true },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Kas Masuk &amp; Keluar
                </h2>
            }
        >
            <Head title="Kas Masuk & Keluar" />

            <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex flex-wrap gap-2">
                    <SelectInput
                        value={filters.month}
                        onChange={(e) => updateFilter('month', e.target.value)}
                    >
                        {MONTHS.map((name, index) => (
                            <option key={name} value={index + 1}>
                                {name}
                            </option>
                        ))}
                    </SelectInput>
                    <SelectInput
                        value={filters.year}
                        onChange={(e) => updateFilter('year', e.target.value)}
                    >
                        {years.map((year) => (
                            <option key={year} value={year}>
                                {year}
                            </option>
                        ))}
                    </SelectInput>
                    <SelectInput
                        value={filters.type}
                        onChange={(e) => updateFilter('type', e.target.value)}
                    >
                        <option value="">Semua Jenis</option>
                        <option value="in">Kas Masuk</option>
                        <option value="out">Kas Keluar</option>
                    </SelectInput>
                </div>

                <div className="flex gap-2">
                    <Link href={route('treasury-report.index')}>
                        <SecondaryButton>Lihat Laporan</SecondaryButton>
                    </Link>
                    <Link href={route('treasuries.create')}>
                        <PrimaryButton>+ Catat Transaksi</PrimaryButton>
                    </Link>
                </div>
            </div>

            <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <table className="min-w-full divide-y divide-gray-100 text-sm">
                    <thead className="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                        <tr>
                            <th className="px-4 py-3">Tanggal</th>
                            <th className="px-4 py-3">Jenis</th>
                            <th className="px-4 py-3">Kategori</th>
                            <th className="px-4 py-3">Keterangan</th>
                            <th className="px-4 py-3 text-right">Jumlah</th>
                            <th className="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {treasuries.data.map((t) => (
                            <tr key={t.id}>
                                <td className="px-4 py-3 text-gray-600">
                                    {formatDate(t.transaction_date)}
                                </td>
                                <td className="px-4 py-3">
                                    <span
                                        className={
                                            'rounded px-2 py-0.5 text-xs font-medium ' +
                                            (t.type === 'in'
                                                ? 'bg-emerald-50 text-emerald-700'
                                                : 'bg-rose-50 text-rose-700')
                                        }
                                    >
                                        {t.type === 'in' ? 'Masuk' : 'Keluar'}
                                    </span>
                                </td>
                                <td className="px-4 py-3 text-gray-600">{t.category?.name}</td>
                                <td className="px-4 py-3 text-gray-600">{t.description}</td>
                                <td className="px-4 py-3 text-right font-medium text-gray-900">
                                    {formatCurrency(t.amount)}
                                </td>
                                <td className="px-4 py-3 text-right">
                                    <Link
                                        href={route('treasuries.edit', t.id)}
                                        className="text-sm font-medium text-emerald-700 hover:underline"
                                    >
                                        Edit
                                    </Link>
                                </td>
                            </tr>
                        ))}
                        {treasuries.data.length === 0 && (
                            <tr>
                                <td colSpan={6} className="px-4 py-6 text-center text-gray-500">
                                    Tidak ada transaksi pada periode ini.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <div className="mt-4">
                <Pagination links={treasuries.links} />
            </div>
        </AuthenticatedLayout>
    );
}
