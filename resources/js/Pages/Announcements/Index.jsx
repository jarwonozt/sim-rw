import DangerButton from '@/Components/DangerButton';
import Modal from '@/Components/Modal';
import Pagination from '@/Components/Pagination';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

function formatDate(value) {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

export default function Index({ announcements }) {
    const [confirmDelete, setConfirmDelete] = useState(null);

    const destroy = () => {
        router.delete(route('announcements.destroy', confirmDelete.id), {
            onFinish: () => setConfirmDelete(null),
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Pengumuman
                </h2>
            }
        >
            <Head title="Pengumuman" />

            <div className="mb-4 flex justify-end">
                <Link href={route('announcements.create')}>
                    <PrimaryButton>+ Buat Pengumuman</PrimaryButton>
                </Link>
            </div>

            <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <table className="min-w-full divide-y divide-gray-100 text-sm">
                    <thead className="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                        <tr>
                            <th className="px-4 py-3">Judul</th>
                            <th className="px-4 py-3">Tayang</th>
                            <th className="px-4 py-3">Kadaluarsa</th>
                            <th className="px-4 py-3">Penulis</th>
                            <th className="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {announcements.data.map((announcement) => (
                            <tr key={announcement.id}>
                                <td className="px-4 py-3 font-medium text-gray-900">
                                    {announcement.title}
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {formatDate(announcement.publish_date)}
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {formatDate(announcement.expire_date)}
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {announcement.author?.name}
                                </td>
                                <td className="px-4 py-3 text-right">
                                    <Link
                                        href={route('announcements.edit', announcement.id)}
                                        className="mr-3 text-sm font-medium text-emerald-700 hover:underline"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        onClick={() => setConfirmDelete(announcement)}
                                        className="text-sm font-medium text-rose-600 hover:underline"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        ))}
                        {announcements.data.length === 0 && (
                            <tr>
                                <td colSpan={5} className="px-4 py-6 text-center text-gray-500">
                                    Belum ada pengumuman.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <div className="mt-4">
                <Pagination links={announcements.links} />
            </div>

            <Modal show={Boolean(confirmDelete)} onClose={() => setConfirmDelete(null)} maxWidth="md">
                <div className="p-6">
                    <h2 className="text-lg font-semibold text-gray-900">Hapus pengumuman?</h2>
                    <p className="mt-2 text-sm text-gray-600">
                        "{confirmDelete?.title}" akan dihapus permanen dari arsip publik.
                    </p>
                    <div className="mt-6 flex justify-end gap-3">
                        <SecondaryButton onClick={() => setConfirmDelete(null)}>
                            Batal
                        </SecondaryButton>
                        <DangerButton onClick={destroy}>Hapus</DangerButton>
                    </div>
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}
