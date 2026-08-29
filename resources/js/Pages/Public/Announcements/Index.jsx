import Pagination from '@/Components/Pagination';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link } from '@inertiajs/react';

function formatDate(value) {
    return new Date(value).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

export default function Index({ announcements }) {
    return (
        <PublicLayout>
            <Head title="Pengumuman" />

            <h1 className="text-xl font-semibold text-gray-900">Pengumuman RW</h1>
            <p className="mt-1 text-sm text-gray-500">
                Informasi resmi dan arsip pengumuman dari pengurus RW.
            </p>

            <div className="mt-6 space-y-4">
                {announcements.data.map((announcement) => (
                    <Link
                        key={announcement.id}
                        href={route('public-announcements.show', announcement.id)}
                        className="block rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition hover:shadow-md"
                    >
                        {announcement.image && (
                            <img
                                src={`/storage/${announcement.image}`}
                                alt={announcement.title}
                                className="mb-3 h-40 w-full rounded-lg object-cover"
                            />
                        )}
                        <p className="text-xs text-gray-500">
                            {formatDate(announcement.publish_date)}
                        </p>
                        <h2 className="mt-1 text-base font-semibold text-gray-900">
                            {announcement.title}
                        </h2>
                        <p className="mt-1 line-clamp-2 text-sm text-gray-600">
                            {announcement.content}
                        </p>
                    </Link>
                ))}

                {announcements.data.length === 0 && (
                    <p className="rounded-xl border border-gray-100 bg-white p-6 text-center text-sm text-gray-500">
                        Belum ada pengumuman.
                    </p>
                )}
            </div>

            <div className="mt-6">
                <Pagination links={announcements.links} />
            </div>
        </PublicLayout>
    );
}
