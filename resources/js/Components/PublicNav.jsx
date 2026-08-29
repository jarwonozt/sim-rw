import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link, usePage } from '@inertiajs/react';

export default function PublicNav({ maxWidthClass = 'max-w-3xl' }) {
    const user = usePage().props.auth.user;

    return (
        <header className="sticky top-0 z-20 border-b border-white/60 bg-white/70 backdrop-blur-xl">
            <div
                className={`mx-auto flex items-center justify-between px-4 py-4 sm:px-6 ${maxWidthClass}`}
            >
                <Link href="/" className="flex items-center gap-2.5">
                    <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-primary shadow-md shadow-primary/30">
                        <ApplicationLogo className="block h-5 w-auto fill-current text-white" />
                    </span>
                    <span className="text-base font-bold tracking-tight text-slate-900">
                        SIM-RW
                    </span>
                </Link>

                <nav className="flex items-center gap-1">
                    <Link
                        href={route('public-announcements.index')}
                        className="rounded-xl px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-white/70 hover:text-slate-900"
                    >
                        Pengumuman
                    </Link>
                    <Link
                        href={user ? route('dashboard') : route('login')}
                        className="rounded-xl bg-primary px-4 py-1.5 text-sm font-semibold text-white shadow-md shadow-primary/30 transition hover:bg-primary/90"
                    >
                        {user ? 'Dashboard' : 'Masuk'}
                    </Link>
                </nav>
            </div>
        </header>
    );
}
