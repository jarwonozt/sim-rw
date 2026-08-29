import BudgetAllocationChart from '@/Components/BudgetAllocationChart';
import MonthlyTrendChart from '@/Components/MonthlyTrendChart';
import PopulationPyramidChart from '@/Components/PopulationPyramidChart';
import RecentActivityTable from '@/Components/RecentActivityTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

const CARD_STYLE = {
    total_penduduk: {
        label: 'Total Warga',
        accent: 'bg-primary/10 text-primary',
        icon: (
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584a6.062 6.062 0 01-.037-.666l.001-.03m0 0a6.75 6.75 0 0111.998-.001M12 12.75a3 3 0 100-6 3 3 0 000 6z"
            />
        ),
    },
    total_kk: {
        label: 'Total KK',
        accent: 'bg-sky-50 text-sky-600',
        icon: (
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"
            />
        ),
    },
    total_saldo_kas: {
        label: 'Kas RW',
        accent: 'bg-accent/10 text-accent',
        icon: (
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
        ),
    },
    total_pengaduan_pending: {
        label: 'Pengaduan',
        accent: 'bg-rose-50 text-rose-600',
        icon: (
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"
            />
        ),
    },
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
        <div className="group rounded-2xl border border-slate-100 bg-white p-5 shadow-sm shadow-slate-200/50 transition duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-lg hover:shadow-slate-200/70">
            <div
                className={`inline-flex h-11 w-11 items-center justify-center rounded-xl ${style.accent}`}
            >
                <svg
                    className="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    strokeWidth={1.75}
                    stroke="currentColor"
                >
                    {style.icon}
                </svg>
            </div>
            <p className="mt-4 text-sm font-medium text-slate-500">{style.label}</p>
            <p className="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                {formatValue(statKey, value)}
            </p>
        </div>
    );
}

function Panel({ title, children }) {
    return (
        <div className="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm shadow-slate-200/50">
            <h3 className="text-sm font-semibold text-slate-900">{title}</h3>
            <div className="mt-4">{children}</div>
        </div>
    );
}

export default function Dashboard({
    stats,
    populationPyramid,
    monthlyTrend,
    budgetAllocation,
    recentActivity,
}) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-bold tracking-tight text-slate-900">
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

            {monthlyTrend && budgetAllocation && (
                <div className="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div className="lg:col-span-2">
                        <Panel title="Tren Kas Bulanan">
                            <MonthlyTrendChart data={monthlyTrend} />
                        </Panel>
                    </div>
                    <Panel title="Alokasi Anggaran Bulan Ini">
                        <BudgetAllocationChart data={budgetAllocation} />
                    </Panel>
                </div>
            )}

            <div className="mt-6">
                <Panel title="Piramida Penduduk">
                    <PopulationPyramidChart data={populationPyramid} />
                </Panel>
            </div>

            {recentActivity && (
                <div className="mt-6">
                    <Panel title="Aktivitas Terbaru">
                        <RecentActivityTable activities={recentActivity} />
                    </Panel>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
