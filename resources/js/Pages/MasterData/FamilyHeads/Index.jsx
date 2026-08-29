import Pagination from '@/Components/Pagination';
import PrimaryButton from '@/Components/PrimaryButton';
import ResidentImportModal from '@/Components/ResidentImportModal';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ familyHeads, filters }) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [showImport, setShowImport] = useState(false);
    const importErrors = usePage().props.flash?.importErrors;

    const submitSearch = (e) => {
        e.preventDefault();
        router.get(route('family-heads.index'), { search }, { preserveState: true });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Kepala Keluarga
                </h2>
            }
        >
            <Head title="Kepala Keluarga" />

            <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form onSubmit={submitSearch} className="flex gap-2">
                    <TextInput
                        placeholder="Cari No. KK atau alamat..."
                        className="w-64"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                    />
                    <PrimaryButton type="submit">Cari</PrimaryButton>
                </form>

                <div className="flex gap-2">
                    <a href={route('residents.export')}>
                        <SecondaryButton>Export Excel</SecondaryButton>
                    </a>
                    <SecondaryButton onClick={() => setShowImport(true)}>
                        Import Excel
                    </SecondaryButton>
                    <Link href={route('family-heads.create')}>
                        <PrimaryButton>+ Tambah KK</PrimaryButton>
                    </Link>
                </div>
            </div>

            {importErrors && importErrors.length > 0 && (
                <div className="mb-4 rounded-lg bg-amber-50 p-4 text-sm text-amber-800">
                    <p className="font-medium">
                        {importErrors.length} baris dilewati saat import:
                    </p>
                    <ul className="mt-1 list-inside list-disc space-y-0.5">
                        {importErrors.slice(0, 10).map((message, index) => (
                            <li key={index}>{message}</li>
                        ))}
                        {importErrors.length > 10 && (
                            <li>...dan {importErrors.length - 10} lainnya.</li>
                        )}
                    </ul>
                </div>
            )}

            <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <table className="min-w-full divide-y divide-gray-100 text-sm">
                    <thead className="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                        <tr>
                            <th className="px-4 py-3">No. KK</th>
                            <th className="px-4 py-3">Alamat</th>
                            <th className="px-4 py-3">RT</th>
                            <th className="px-4 py-3">Jml. Anggota</th>
                            <th className="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {familyHeads.data.map((fh) => (
                            <tr key={fh.id}>
                                <td className="px-4 py-3 font-medium text-gray-900">
                                    {fh.no_kk}
                                </td>
                                <td className="px-4 py-3 text-gray-600">{fh.address}</td>
                                <td className="px-4 py-3 text-gray-600">
                                    RT {fh.rt?.nomor_rt ?? '—'}
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {fh.residents_count}
                                </td>
                                <td className="px-4 py-3 text-right">
                                    <Link
                                        href={route('family-heads.show', fh.id)}
                                        className="text-sm font-medium text-emerald-700 hover:underline"
                                    >
                                        Detail
                                    </Link>
                                </td>
                            </tr>
                        ))}
                        {familyHeads.data.length === 0 && (
                            <tr>
                                <td colSpan={5} className="px-4 py-6 text-center text-gray-500">
                                    Tidak ada data KK.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <div className="mt-4">
                <Pagination links={familyHeads.links} />
            </div>

            <ResidentImportModal show={showImport} onClose={() => setShowImport(false)} />
        </AuthenticatedLayout>
    );
}
