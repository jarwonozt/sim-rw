import DangerButton from '@/Components/DangerButton';
import Modal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import ResidentFormModal from '@/Components/ResidentFormModal';
import SecondaryButton from '@/Components/SecondaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

function formatDate(value) {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

export default function Show({ familyHead }) {
    const [residentForm, setResidentForm] = useState({ show: false, resident: null });
    const [confirmDelete, setConfirmDelete] = useState(null);

    const deleteResident = () => {
        router.delete(route('residents.destroy', confirmDelete.id), {
            onFinish: () => setConfirmDelete(null),
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Detail Kepala Keluarga
                </h2>
            }
        >
            <Head title={`KK ${familyHead.no_kk}`} />

            <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p className="text-sm text-gray-500">No. KK</p>
                        <p className="text-lg font-semibold text-gray-900">
                            {familyHead.no_kk}
                        </p>
                        <p className="mt-2 text-sm text-gray-600">
                            {familyHead.address} · RT {familyHead.rt?.nomor_rt}
                            {familyHead.postal_code ? ` · ${familyHead.postal_code}` : ''}
                        </p>
                    </div>
                    <Link href={route('family-heads.edit', familyHead.id)}>
                        <SecondaryButton>Edit KK</SecondaryButton>
                    </Link>
                </div>
            </div>

            <div className="mt-6 flex items-center justify-between">
                <h3 className="text-base font-semibold text-gray-900">
                    Anggota Keluarga
                </h3>
                <PrimaryButton
                    onClick={() => setResidentForm({ show: true, resident: null })}
                >
                    + Tambah Penduduk
                </PrimaryButton>
            </div>

            <div className="mt-3 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <table className="min-w-full divide-y divide-gray-100 text-sm">
                    <thead className="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                        <tr>
                            <th className="px-4 py-3">NIK</th>
                            <th className="px-4 py-3">Nama</th>
                            <th className="px-4 py-3">Hubungan</th>
                            <th className="px-4 py-3">L/P</th>
                            <th className="px-4 py-3">Tgl. Lahir</th>
                            <th className="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {familyHead.residents.map((resident) => (
                            <tr key={resident.id}>
                                <td className="px-4 py-3 font-medium text-gray-900">
                                    {resident.nik}
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {resident.name}
                                    {resident.is_family_head && (
                                        <span className="ml-2 rounded bg-sky-50 px-1.5 py-0.5 text-xs font-medium text-sky-700">
                                            Kepala Keluarga
                                        </span>
                                    )}
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {resident.relationship_status || '—'}
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {resident.gender}
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {formatDate(resident.birth_date)}
                                </td>
                                <td className="px-4 py-3 text-right">
                                    <button
                                        onClick={() =>
                                            setResidentForm({ show: true, resident })
                                        }
                                        className="mr-3 text-sm font-medium text-emerald-700 hover:underline"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        onClick={() => setConfirmDelete(resident)}
                                        className="text-sm font-medium text-rose-600 hover:underline"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        ))}
                        {familyHead.residents.length === 0 && (
                            <tr>
                                <td colSpan={6} className="px-4 py-6 text-center text-gray-500">
                                    Belum ada anggota keluarga.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <ResidentFormModal
                show={residentForm.show}
                resident={residentForm.resident}
                familyHeadId={familyHead.id}
                onClose={() => setResidentForm({ show: false, resident: null })}
            />

            <Modal show={Boolean(confirmDelete)} onClose={() => setConfirmDelete(null)} maxWidth="md">
                <div className="p-6">
                    <h2 className="text-lg font-semibold text-gray-900">
                        Hapus data penduduk?
                    </h2>
                    <p className="mt-2 text-sm text-gray-600">
                        {confirmDelete?.name} akan dihapus permanen dari KK ini.
                    </p>
                    <div className="mt-6 flex justify-end gap-3">
                        <SecondaryButton onClick={() => setConfirmDelete(null)}>
                            Batal
                        </SecondaryButton>
                        <DangerButton onClick={deleteResident}>Hapus</DangerButton>
                    </div>
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}
