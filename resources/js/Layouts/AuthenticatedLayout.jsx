import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

const ROLE_LABELS = {
    super_admin: 'Super Admin',
    ketua_rw: 'Ketua RW',
    sekretaris: 'Sekretaris',
    bendahara: 'Bendahara',
    ketua_rt: 'Ketua RT',
    warga: 'Warga',
};

/**
 * Setiap item hanya tampil untuk role yang terdaftar di `roles`.
 * Tambahkan route baru di sini begitu modulnya tersedia (Surat, Keuangan, dst).
 */
const NAVIGATION = [
    {
        name: 'Dashboard',
        route: 'dashboard',
        roles: ['super_admin', 'ketua_rw', 'sekretaris', 'bendahara', 'ketua_rt', 'warga'],
        icon: (
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
            />
        ),
    },
    {
        name: 'Profil RW',
        route: 'rw.edit',
        roles: ['super_admin', 'ketua_rw'],
        icon: (
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"
            />
        ),
    },
    {
        name: 'Data RT',
        route: 'rt.index',
        roles: ['super_admin', 'ketua_rw'],
        icon: (
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"
            />
        ),
    },
    {
        name: 'Kepala Keluarga',
        route: 'family-heads.index',
        roles: ['super_admin', 'ketua_rw', 'sekretaris', 'ketua_rt'],
        icon: (
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"
            />
        ),
    },
    {
        name: 'Surat Menyurat',
        route: 'letters.index',
        roles: ['super_admin', 'ketua_rw', 'sekretaris'],
        icon: (
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"
            />
        ),
    },
    {
        name: 'Template Surat',
        route: 'letter-templates.index',
        roles: ['super_admin', 'ketua_rw', 'sekretaris'],
        icon: (
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.014 8.25 4.977 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"
            />
        ),
    },
    {
        name: 'Kas Masuk & Keluar',
        route: 'treasuries.index',
        roles: ['super_admin', 'ketua_rw', 'bendahara'],
        icon: (
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
        ),
    },
    {
        name: 'Laporan Kas',
        route: 'treasury-report.index',
        roles: ['super_admin', 'ketua_rw', 'bendahara'],
        icon: (
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z"
            />
        ),
    },
    {
        name: 'Kategori Kas',
        route: 'treasury-categories.index',
        roles: ['super_admin', 'ketua_rw', 'bendahara'],
        icon: (
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M6 6.878V6a2.25 2.25 0 012.25-2.25h7.5A2.25 2.25 0 0118 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 004.5 9v.878m13.5-3A2.25 2.25 0 0119.5 9v.878m0 0a2.246 2.246 0 00-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0121 12v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6c0-.98.626-1.813 1.5-2.122"
            />
        ),
    },
    {
        name: 'Pengaduan',
        route: 'complaints.index',
        roles: ['super_admin', 'ketua_rw', 'ketua_rt', 'warga'],
        icon: (
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"
            />
        ),
    },
    {
        name: 'Pengumuman',
        route: 'announcements.index',
        roles: ['super_admin', 'ketua_rw'],
        icon: (
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.46c.495.413.811 1.035.811 1.73s-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 010 3.46"
            />
        ),
    },
];

function SidebarLink({ item, onNavigate }) {
    const routePrefix = item.route.split('.')[0];
    const active = route().current(`${routePrefix}.*`) || route().current(routePrefix);

    return (
        <Link
            href={route(item.route)}
            onClick={onNavigate}
            className={
                'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition ' +
                (active
                    ? 'bg-primary text-white shadow-md shadow-primary/30'
                    : 'text-slate-600 hover:bg-white/70 hover:text-slate-900')
            }
        >
            <svg
                className="h-5 w-5 shrink-0"
                fill="none"
                viewBox="0 0 24 24"
                strokeWidth={1.75}
                stroke="currentColor"
            >
                {item.icon}
            </svg>
            {item.name}
        </Link>
    );
}

function SidebarContent({ user, onNavigate }) {
    const items = NAVIGATION.filter(
        (item) => !item.roles || item.roles.includes(user.role),
    );

    return (
        <div className="flex h-full flex-col">
            <Link href="/" className="flex items-center gap-2.5 px-5 py-6">
                <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-primary shadow-md shadow-primary/30">
                    <ApplicationLogo className="block h-5 w-auto fill-current text-white" />
                </span>
                <span className="text-base font-bold tracking-tight text-slate-900">
                    SIM-RW
                </span>
            </Link>

            <nav className="flex-1 space-y-1 overflow-y-auto px-3 pb-4">
                {items.map((item) => (
                    <SidebarLink key={item.name} item={item} onNavigate={onNavigate} />
                ))}
            </nav>

            <div className="m-3 rounded-xl bg-white/70 p-3">
                <div className="flex items-center gap-3">
                    <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary">
                        {user.name.charAt(0).toUpperCase()}
                    </span>
                    <div className="min-w-0">
                        <p className="truncate text-sm font-semibold text-slate-900">
                            {user.name}
                        </p>
                        <p className="truncate text-xs text-slate-500">
                            {ROLE_LABELS[user.role] ?? user.role}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}

function FlashMessages() {
    const flash = usePage().props.flash ?? {};

    if (!flash.success && !flash.error) {
        return null;
    }

    return (
        <div className="mb-6 space-y-2">
            {flash.success && (
                <div className="rounded-xl border-1 border-primary bg-primary/5 px-4 py-3 text-sm font-medium text-primary">
                    {flash.success}
                </div>
            )}
            {flash.error && (
                <div className="rounded-2xl border-l-4 border-rose-500 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                    {flash.error}
                </div>
            )}
        </div>
    );
}

export default function AuthenticatedLayout({ header, children }) {
    const user = usePage().props.auth.user;
    const [sidebarOpen, setSidebarOpen] = useState(false);

    return (
        <div className="min-h-screen bg-slate-100 text-[15px] text-slate-900">
            {/* Dekorasi latar untuk efek glassmorphism pada sidebar/navbar */}
            <div className="pointer-events-none fixed inset-0 overflow-hidden">
                <div className="absolute -left-24 -top-24 h-96 w-96 rounded-full bg-primary/10 blur-3xl" />
                <div className="absolute -right-24 top-1/3 h-96 w-96 rounded-full bg-accent/10 blur-3xl" />
            </div>

            {/* Sidebar (desktop, fixed + glassmorphism) */}
            <aside className="fixed inset-y-0 left-0 z-30 hidden w-64 border-r border-white/60 bg-white/70 shadow-xl shadow-slate-200/50 backdrop-blur-xl lg:block">
                <SidebarContent user={user} />
            </aside>

            {/* Sidebar (mobile, collapse otomatis) */}
            {sidebarOpen && (
                <div className="fixed inset-0 z-40 lg:hidden">
                    <div
                        className="absolute inset-0 bg-slate-900/40"
                        onClick={() => setSidebarOpen(false)}
                    />
                    <aside className="absolute inset-y-0 left-0 w-64 bg-white/90 shadow-xl backdrop-blur-xl">
                        <SidebarContent
                            user={user}
                            onNavigate={() => setSidebarOpen(false)}
                        />
                    </aside>
                </div>
            )}

            <div className="relative flex min-h-screen flex-col lg:pl-64">
                <header className="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-white/60 bg-white/70 px-4 backdrop-blur-xl sm:px-6">
                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            onClick={() => setSidebarOpen(true)}
                            className="rounded-xl p-2 text-slate-500 hover:bg-white/70 lg:hidden"
                        >
                            <svg
                                className="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                            </svg>
                        </button>
                        {header}
                    </div>

                    <Dropdown>
                        <Dropdown.Trigger>
                            <button
                                type="button"
                                className="flex items-center gap-2 rounded-xl px-2 py-1.5 text-sm font-medium text-slate-600 hover:bg-white/70"
                            >
                                <span className="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary">
                                    {user.name.charAt(0).toUpperCase()}
                                </span>
                                <span className="hidden sm:inline">{user.name}</span>
                                <svg
                                    className="h-4 w-4"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fillRule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clipRule="evenodd"
                                    />
                                </svg>
                            </button>
                        </Dropdown.Trigger>

                        <Dropdown.Content>
                            <Dropdown.Link href={route('profile.edit')}>
                                Profil Saya
                            </Dropdown.Link>
                            <Dropdown.Link
                                href={route('logout')}
                                method="post"
                                as="button"
                            >
                                Keluar
                            </Dropdown.Link>
                        </Dropdown.Content>
                    </Dropdown>
                </header>

                <main className="relative flex-1 p-4 sm:p-6">
                    <FlashMessages />
                    {children}
                </main>
            </div>
        </div>
    );
}
