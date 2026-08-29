import PublicNav from '@/Components/PublicNav';

export default function PublicLayout({ children }) {
    return (
        <div className="min-h-screen bg-slate-100 text-[15px] text-slate-900">
            <PublicNav />

            <main className="mx-auto max-w-3xl px-4 py-8 sm:px-6">{children}</main>
        </div>
    );
}
