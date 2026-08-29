import Pagination from '@/Components/Pagination';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
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

export default function Index({ letters, filters }) {
    const [search, setSearch] = useState(filters.search ?? '');

    const submitSearch = (e) => {
        e.preventDefault();
        router.get(route('letters.index'), { search }, { preserveState: true });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Buku Agenda Surat
                </h2>
            }
        >
            <Head title="Buku Agenda Surat" />

            <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form onSubmit={submitSearch} className="flex gap-2">
                    <TextInput
                        placeholder="Cari nomor surat, nama, atau tujuan..."
                        className="w-72"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                    />
                    <PrimaryButton type="submit">Cari</PrimaryButton>
                </form>

                <Link href={route('letters.create')}>
                    <PrimaryButton>+ Buat Surat</PrimaryButton>
                </Link>
            </div>

            <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <table className="min-w-full divide-y divide-gray-100 text-sm">
                    <thead className="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                        <tr>
                            <th className="px-4 py-3">Nomor Surat</th>
                            <th className="px-4 py-3">Tanggal</th>
                            <th className="px-4 py-3">Jenis</th>
                            <th className="px-4 py-3">Penduduk</th>
                            <th className="px-4 py-3">Tujuan</th>
                            <th className="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {letters.data.map((letter) => (
                            <tr key={letter.id}>
                                <td className="px-4 py-3 font-medium text-gray-900">
                                    {letter.letter_number}
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {formatDate(letter.issued_date)}
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {letter.template?.name}
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {letter.resident?.name}
                                </td>
                                <td className="px-4 py-3 text-gray-600">{letter.purpose}</td>
                                <td className="px-4 py-3 text-right">
                                    <Link
                                        href={route('letters.show', letter.id)}
                                        className="text-sm font-medium text-emerald-700 hover:underline"
                                    >
                                        Detail
                                    </Link>
                                </td>
                            </tr>
                        ))}
                        {letters.data.length === 0 && (
                            <tr>
                                <td colSpan={6} className="px-4 py-6 text-center text-gray-500">
                                    Belum ada surat yang diterbitkan.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <div className="mt-4">
                <Pagination links={letters.links} />
            </div>
        </AuthenticatedLayout>
    );
}
