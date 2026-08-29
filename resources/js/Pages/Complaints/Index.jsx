import Pagination from '@/Components/Pagination';
import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { STATUS_COLORS, STATUS_LABELS } from '@/Utils/complaintStatus';
import { Head, Link, usePage } from '@inertiajs/react';

function formatDate(value) {
    return new Date(value).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

export default function Index({ complaints }) {
    const user = usePage().props.auth.user;

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    {user.role === 'warga' ? 'Pengaduan Saya' : 'Daftar Pengaduan'}
                </h2>
            }
        >
            <Head title="Pengaduan" />

            <div className="mb-4 flex justify-end">
                <Link href={route('complaints.create')}>
                    <PrimaryButton>+ Ajukan Pengaduan</PrimaryButton>
                </Link>
            </div>

            <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <table className="min-w-full divide-y divide-gray-100 text-sm">
                    <thead className="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                        <tr>
                            <th className="px-4 py-3">Tanggal</th>
                            <th className="px-4 py-3">Judul</th>
                            {user.role !== 'warga' && <th className="px-4 py-3">Pelapor</th>}
                            <th className="px-4 py-3">RT</th>
                            <th className="px-4 py-3">Status</th>
                            <th className="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {complaints.data.map((complaint) => (
                            <tr key={complaint.id}>
                                <td className="px-4 py-3 text-gray-600">
                                    {formatDate(complaint.created_at)}
                                </td>
                                <td className="px-4 py-3 font-medium text-gray-900">
                                    {complaint.title}
                                </td>
                                {user.role !== 'warga' && (
                                    <td className="px-4 py-3 text-gray-600">
                                        {complaint.user?.name}
                                    </td>
                                )}
                                <td className="px-4 py-3 text-gray-600">
                                    RT {complaint.rt?.nomor_rt}
                                </td>
                                <td className="px-4 py-3">
                                    <span
                                        className={
                                            'rounded px-2 py-0.5 text-xs font-medium ' +
                                            STATUS_COLORS[complaint.status]
                                        }
                                    >
                                        {STATUS_LABELS[complaint.status]}
                                    </span>
                                </td>
                                <td className="px-4 py-3 text-right">
                                    <Link
                                        href={route('complaints.show', complaint.id)}
                                        className="text-sm font-medium text-emerald-700 hover:underline"
                                    >
                                        Detail
                                    </Link>
                                </td>
                            </tr>
                        ))}
                        {complaints.data.length === 0 && (
                            <tr>
                                <td colSpan={6} className="px-4 py-6 text-center text-gray-500">
                                    Belum ada pengaduan.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <div className="mt-4">
                <Pagination links={complaints.links} />
            </div>
        </AuthenticatedLayout>
    );
}
