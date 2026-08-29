import PublicNav from '@/Components/PublicNav';
import { Head, Link } from '@inertiajs/react';

const FEATURES = [
    {
        name: 'Data Warga Terpadu',
        description:
            'Satu sumber data untuk Kepala Keluarga dan Penduduk, terorganisir per RT sehingga mudah dicari dan dilaporkan.',
        accent: 'bg-primary/10 text-primary',
        icon: (
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584a6.062 6.062 0 01-.037-.666l.001-.03m0 0a6.75 6.75 0 0111.998-.001M12 12.75a3 3 0 100-6 3 3 0 000 6z"
            />
        ),
    },
    {
        name: 'Surat Menyurat Digital',
        description:
            'Terbitkan surat pengantar (Domisili, SKTM, Usaha) dalam hitungan menit, lengkap dengan buku agenda otomatis.',
        accent: 'bg-sky-50 text-sky-600',
        icon: (
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"
            />
        ),
    },
    {
        name: 'Transparansi Keuangan',
        description:
            'Catat kas masuk dan keluar dengan bukti foto, lihat laporan dan alokasi anggaran secara real-time.',
        accent: 'bg-accent/10 text-accent',
        icon: (
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
        ),
    },
    {
        name: 'Pengaduan & Pengumuman',
        description:
            'Warga dapat mengajukan pengaduan dan memantau statusnya, serta membaca pengumuman resmi tanpa perlu login.',
        accent: 'bg-rose-50 text-rose-600',
        icon: (
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"
            />
        ),
    },
];

export default function Welcome({ canLogin, canRegister }) {
    return (
        <div className="min-h-screen bg-slate-100 text-[15px] text-slate-900">
            <Head title="Selamat Datang" />

            {/* Dekorasi latar */}
            <div className="pointer-events-none fixed inset-0 overflow-hidden">
                <div className="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-primary/10 blur-3xl" />
                <div className="absolute -right-32 top-1/4 h-96 w-96 rounded-full bg-accent/10 blur-3xl" />
            </div>

            <div className="relative">
                <PublicNav maxWidthClass="max-w-6xl" />

                <main className="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-24">
                    <section className="mx-auto max-w-2xl text-center">
                        <span className="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                            Sistem Informasi Manajemen RW
                        </span>
                        <h1 className="mt-5 text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl">
                            Administrasi RW yang{' '}
                            <span className="text-primary">rapi</span>, cepat, dan{' '}
                            <span className="text-accent">transparan</span>
                        </h1>
                        <p className="mt-5 text-base text-slate-600 sm:text-lg">
                            Kelola data warga, surat-menyurat, kas RW, dan pengaduan
                            warga dalam satu sistem — menggantikan buku tulis dan
                            catatan Excel yang tercecer.
                        </p>

                        <div className="mt-8 flex flex-wrap items-center justify-center gap-3">
                            {canLogin && (
                                <Link
                                    href={route('login')}
                                    className="rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-primary/30 transition hover:bg-primary/90"
                                >
                                    Masuk ke Sistem
                                </Link>
                            )}
                            <Link
                                href={route('public-announcements.index')}
                                className="rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-white/70"
                            >
                                Lihat Pengumuman
                            </Link>
                        </div>

                        {canRegister && (
                            <p className="mt-4 text-sm text-slate-500">
                                Warga baru?{' '}
                                <Link
                                    href={route('register')}
                                    className="font-medium text-primary hover:underline"
                                >
                                    Daftar akun
                                </Link>
                            </p>
                        )}
                    </section>

                    <section className="mt-20 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {FEATURES.map((feature) => (
                            <div
                                key={feature.name}
                                className="group rounded-2xl border border-slate-100 bg-white p-6 shadow-sm shadow-slate-200/50 transition duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-lg hover:shadow-slate-200/70"
                            >
                                <div
                                    className={`inline-flex h-11 w-11 items-center justify-center rounded-xl ${feature.accent}`}
                                >
                                    <svg
                                        className="h-6 w-6"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        strokeWidth={1.75}
                                        stroke="currentColor"
                                    >
                                        {feature.icon}
                                    </svg>
                                </div>
                                <h3 className="mt-4 text-sm font-semibold text-slate-900">
                                    {feature.name}
                                </h3>
                                <p className="mt-1.5 text-sm text-slate-600">
                                    {feature.description}
                                </p>
                            </div>
                        ))}
                    </section>
                </main>

                <footer className="border-t border-white/60 py-8 text-center text-sm text-slate-500">
                    © {new Date().getFullYear()} SIM-RW — Sistem Informasi Manajemen RW.
                </footer>
            </div>
        </div>
    );
}
