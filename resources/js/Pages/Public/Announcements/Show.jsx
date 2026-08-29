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
                className="text-sm font-medium text-primary hover:underline"
            >
                &larr; Kembali ke daftar pengumuman
            </Link>

            <article className="mt-4 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm shadow-slate-200/50">
                {announcement.image && (
                    <img
                        src={`/storage/${announcement.image}`}
                        alt={announcement.title}
                        className="mb-4 max-h-96 w-full rounded-xl object-cover"
                    />
                )}
                <p className="text-xs text-slate-500">{formatDate(announcement.publish_date)}</p>
                <h1 className="mt-1 text-xl font-bold tracking-tight text-slate-900">
                    {announcement.title}
                </h1>
                <p className="mt-4 whitespace-pre-line text-sm text-slate-700">
                    {announcement.content}
                </p>
            </article>
        </PublicLayout>
    );
}
