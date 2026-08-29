import { ArcElement, Chart as ChartJS, Legend, Tooltip } from 'chart.js';
import { Pie } from 'react-chartjs-2';

ChartJS.register(ArcElement, Tooltip, Legend);

const PALETTE = ['#0d7c66', '#f59e0b', '#0ea5e9', '#ef4444', '#8b5cf6', '#14b8a6', '#ec4899', '#84cc16'];

export default function BudgetAllocationChart({ data }) {
    if (data.length === 0) {
        return (
            <p className="text-sm text-slate-500">
                Belum ada pengeluaran bulan ini untuk dialokasikan.
            </p>
        );
    }

    const chartData = {
        labels: data.map((row) => row.name),
        datasets: [
            {
                data: data.map((row) => row.total),
                backgroundColor: PALETTE,
                borderWidth: 0,
            },
        ],
    };

    const options = {
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 12 } } },
            tooltip: {
                callbacks: {
                    label: (context) =>
                        `${context.label}: Rp ${new Intl.NumberFormat('id-ID').format(context.parsed)}`,
                },
            },
        },
    };

    return (
        <div className="h-72">
            <Pie data={chartData} options={options} />
        </div>
    );
}
