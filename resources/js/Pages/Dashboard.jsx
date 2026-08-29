import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

const CARD_STYLE = {
    total_kk: { label: 'Total KK', accent: 'text-sky-600 bg-sky-50' },
    total_penduduk: { label: 'Total Penduduk', accent: 'text-emerald-600 bg-emerald-50' },
    total_saldo_kas: { label: 'Total Saldo Kas', accent: 'text-amber-600 bg-amber-50' },
    total_pengaduan_pending: { label: 'Pengaduan Pending', accent: 'text-rose-600 bg-rose-50' },
};

function formatValue(key, value) {
    if (value === null || value === undefined) {
        return '—';
    }

    if (key === 'total_saldo_kas') {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        }).format(value);
    }

    return new Intl.NumberFormat('id-ID').format(value);
}

function StatCard({ statKey, value }) {
    const style = CARD_STYLE[statKey];

    return (
        <div className="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <div
                className={`inline-flex rounded-lg px-2.5 py-1 text-sm font-medium ${style.accent}`}
            >
                {style.label}
            </div>
            <p className="mt-4 text-3xl font-semibold text-gray-900">
                {formatValue(statKey, value)}
            </p>
        </div>
    );
}

export default function Dashboard({ stats }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {Object.keys(CARD_STYLE).map((key) => (
                    <StatCard key={key} statKey={key} value={stats[key]} />
                ))}
            </div>
        </AuthenticatedLayout>
    );
}
