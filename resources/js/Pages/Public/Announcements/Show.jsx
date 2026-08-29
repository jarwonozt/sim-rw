import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link } from '@inertiajs/react';

function formatDate(value) {
    return new Date(value).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

export default function Show({ announcement }) {
    return (
        <PublicLayout>
            <Head title={announcement.title} />

            <Link
                href={route('public-announcements.index')}
                className="text-sm font-medium text-emerald-700 hover:underline"
            >
                &larr; Kembali ke daftar pengumuman
            </Link>

            <article className="mt-4 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                {announcement.image && (
                    <img
                        src={`/storage/${announcement.image}`}
                        alt={announcement.title}
                        className="mb-4 max-h-96 w-full rounded-lg object-cover"
                    />
                )}
                <p className="text-xs text-gray-500">{formatDate(announcement.publish_date)}</p>
                <h1 className="mt-1 text-xl font-semibold text-gray-900">
                    {announcement.title}
                </h1>
                <p className="mt-4 whitespace-pre-line text-sm text-gray-700">
                    {announcement.content}
                </p>
            </article>
        </PublicLayout>
    );
}
