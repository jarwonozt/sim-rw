import {
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    Tooltip,
} from 'chart.js';
import { Bar } from 'react-chartjs-2';

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend);

function formatCompact(value) {
    return new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);
}

export default function MonthlyTrendChart({ data }) {
    const hasData = data.some((row) => row.in > 0 || row.out > 0);

    if (!hasData) {
        return (
            <p className="text-sm text-slate-500">
                Belum ada transaksi kas pada periode ini.
            </p>
        );
    }

    const chartData = {
        labels: data.map((row) => row.month),
        datasets: [
            {
                label: 'Kas Masuk',
                data: data.map((row) => row.in),
                backgroundColor: '#0d7c66',
                borderRadius: 6,
                maxBarThickness: 28,
            },
            {
                label: 'Kas Keluar',
                data: data.map((row) => row.out),
                backgroundColor: '#f59e0b',
                borderRadius: 6,
                maxBarThickness: 28,
            },
        ],
    };

    const options = {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: { grid: { display: false } },
            y: {
                ticks: { callback: (value) => formatCompact(value) },
                grid: { color: '#e2e8f0' },
            },
        },
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 12 } } },
            tooltip: {
                callbacks: {
                    label: (context) =>
                        `${context.dataset.label}: Rp ${new Intl.NumberFormat('id-ID').format(context.parsed.y)}`,
                },
            },
        },
    };

    return (
        <div className="h-72">
            <Bar data={chartData} options={options} />
        </div>
    );
}
