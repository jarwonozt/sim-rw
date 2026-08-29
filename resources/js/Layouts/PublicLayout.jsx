import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link, usePage } from '@inertiajs/react';

export default function PublicLayout({ children }) {
    const user = usePage().props.auth.user;

    return (
        <div className="min-h-screen bg-gray-50 text-[15px]">
            <header className="border-b border-gray-100 bg-white">
                <div className="mx-auto flex max-w-3xl items-center justify-between px-4 py-4 sm:px-6">
                    <Link href="/" className="flex items-center gap-2">
                        <ApplicationLogo className="block h-8 w-auto fill-current text-emerald-700" />
                        <span className="text-base font-semibold text-gray-900">SIM-RW</span>
                    </Link>

                    <Link
                        href={user ? route('dashboard') : route('login')}
                        className="text-sm font-medium text-gray-600 hover:text-gray-900"
                    >
                        {user ? 'Dashboard' : 'Masuk'}
                    </Link>
                </div>
            </header>

            <main className="mx-auto max-w-3xl px-4 py-8 sm:px-6">{children}</main>
        </div>
    );
}
