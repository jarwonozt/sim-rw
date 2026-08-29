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

export default function PopulationPyramidChart({ data }) {
    const hasData = data.some((row) => row.male > 0 || row.female > 0);

    if (!hasData) {
        return (
            <p className="text-sm text-gray-500">
                Belum ada data tanggal lahir penduduk untuk ditampilkan.
            </p>
        );
    }

    // Termuda di bawah, tertua di atas — konvensi piramida penduduk.
    const ordered = [...data].reverse();

    const chartData = {
        labels: ordered.map((row) => row.age_band),
        datasets: [
            {
                label: 'Laki-laki',
                data: ordered.map((row) => -row.male),
                backgroundColor: '#0ea5e9',
            },
            {
                label: 'Perempuan',
                data: ordered.map((row) => row.female),
                backgroundColor: '#ec4899',
            },
        ],
    };

    const options = {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                stacked: true,
                ticks: {
                    callback: (value) => Math.abs(value),
                },
            },
            y: {
                stacked: true,
            },
        },
        plugins: {
            legend: { position: 'bottom' },
            tooltip: {
                callbacks: {
                    label: (context) =>
                        `${context.dataset.label}: ${Math.abs(context.parsed.x)}`,
                },
            },
        },
    };

    return (
        <div style={{ height: `${ordered.length * 24 + 60}px` }}>
            <Bar data={chartData} options={options} />
        </div>
    );
}
