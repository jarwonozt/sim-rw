import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

function formatDate(value) {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

export default function Show({ letter }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Detail Surat
                </h2>
            }
        >
            <Head title={letter.letter_number} />

            <div className="max-w-xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <p className="text-sm text-gray-500">Nomor Surat</p>
                <p className="text-lg font-semibold text-gray-900">
                    {letter.letter_number}
                </p>

                <dl className="mt-4 space-y-3 text-sm">
                    <div className="flex justify-between">
                        <dt className="text-gray-500">Jenis Surat</dt>
                        <dd className="font-medium text-gray-900">
                            {letter.template?.name}
                        </dd>
                    </div>
                    <div className="flex justify-between">
                        <dt className="text-gray-500">Atas Nama</dt>
                        <dd className="font-medium text-gray-900">
                            {letter.resident?.name} ({letter.resident?.nik})
                        </dd>
                    </div>
                    <div className="flex justify-between">
                        <dt className="text-gray-500">Tujuan</dt>
                        <dd className="font-medium text-gray-900">{letter.purpose}</dd>
                    </div>
                    <div className="flex justify-between">
                        <dt className="text-gray-500">Tanggal Terbit</dt>
                        <dd className="font-medium text-gray-900">
                            {formatDate(letter.issued_date)}
                        </dd>
                    </div>
                    <div className="flex justify-between">
                        <dt className="text-gray-500">Diterbitkan oleh</dt>
                        <dd className="font-medium text-gray-900">
                            {letter.issuer?.name}
                        </dd>
                    </div>
                </dl>

                <a href={route('letters.download', letter.id)} className="mt-6 block">
                    <PrimaryButton className="w-full justify-center">
                        Unduh PDF
                    </PrimaryButton>
                </a>
            </div>
        </AuthenticatedLayout>
    );
}
