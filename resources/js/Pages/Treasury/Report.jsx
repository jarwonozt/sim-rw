import SecondaryButton from '@/Components/SecondaryButton';
import SelectInput from '@/Components/SelectInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import {
    ArcElement,
    Chart as ChartJS,
    Legend,
    Tooltip,
} from 'chart.js';
import { Pie } from 'react-chartjs-2';

ChartJS.register(ArcElement, Tooltip, Legend);

const MONTHS = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

const PALETTE = ['#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#84cc16'];

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);
}

function CategoryPie({ title, entries }) {
    if (entries.length === 0) {
        return (
            <div className="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                <h3 className="text-sm font-semibold text-gray-900">{title}</h3>
                <p className="mt-4 text-sm text-gray-500">Belum ada data pada periode ini.</p>
            </div>
        );
    }

    const chartData = {
        labels: entries.map((e) => e.name),
        datasets: [
            {
                data: entries.map((e) => e.total),
                backgroundColor: PALETTE,
                borderWidth: 0,
            },
        ],
    };

    return (
        <div className="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h3 className="text-sm font-semibold text-gray-900">{title}</h3>
            <div className="mx-auto mt-4 max-w-[260px]">
                <Pie data={chartData} options={{ plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 12 } } } } }} />
            </div>
        </div>
    );
}

export default function Report({ filters, summary, categoryBreakdown }) {
    const currentYear = new Date().getFullYear();
    const years = Array.from({ length: 6 }, (_, i) => currentYear - i);

    const updateFilter = (key, value) => {
        router.get(
            route('treasury-report.index'),
            { ...filters, [key]: value },
            { preserveState: true },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Laporan Rekapitulasi Kas
                </h2>
            }
        >
            <Head title="Laporan Kas" />

            <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex flex-wrap gap-2">
                    <SelectInput
                        value={filters.month ?? ''}
                        onChange={(e) => updateFilter('month', e.target.value)}
                    >
                        <option value="">Sepanjang Tahun</option>
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
                </div>

                <div className="flex gap-2">
                    <a
                        href={route('treasury-report.export-excel', filters)}
                        className="inline-block"
                    >
                        <SecondaryButton>Export Excel</SecondaryButton>
                    </a>
                    <a
                        href={route('treasury-report.export-pdf', filters)}
                        target="_blank"
                        rel="noreferrer"
                        className="inline-block"
                    >
                        <SecondaryButton>Export PDF</SecondaryButton>
                    </a>
                </div>
            </div>

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div className="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p className="text-sm font-medium text-emerald-700">Total Kas Masuk</p>
                    <p className="mt-2 text-2xl font-semibold text-gray-900">
                        {formatCurrency(summary.total_masuk)}
                    </p>
                </div>
                <div className="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p className="text-sm font-medium text-rose-700">Total Kas Keluar</p>
                    <p className="mt-2 text-2xl font-semibold text-gray-900">
                        {formatCurrency(summary.total_keluar)}
                    </p>
                </div>
                <div className="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p className="text-sm font-medium text-sky-700">Saldo Akhir</p>
                    <p className="mt-2 text-2xl font-semibold text-gray-900">
                        {formatCurrency(summary.saldo_akhir)}
                    </p>
                </div>
            </div>

            <div className="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <CategoryPie title="Kas Masuk per Kategori" entries={categoryBreakdown.in} />
                <CategoryPie title="Kas Keluar per Kategori" entries={categoryBreakdown.out} />
            </div>
        </AuthenticatedLayout>
    );
}
