function formatDateTime(value) {
    return new Date(value).toLocaleString('id-ID', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
}

const ACTION_LABELS = {
    login: 'Masuk',
    'letter.issued': 'Surat',
    'treasury.created': 'Kas',
    'complaint.status_updated': 'Pengaduan',
};

const ACTION_COLORS = {
    login: 'bg-slate-100 text-slate-600',
    'letter.issued': 'bg-sky-50 text-sky-700',
    'treasury.created': 'bg-primary/10 text-primary',
    'complaint.status_updated': 'bg-accent/10 text-accent',
};

export default function RecentActivityTable({ activities }) {
    if (activities.length === 0) {
        return <p className="text-sm text-slate-500">Belum ada aktivitas tercatat.</p>;
    }

    return (
        <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-slate-100 text-sm">
                <thead className="text-left text-xs font-medium uppercase tracking-wide text-slate-400">
                    <tr>
                        <th className="py-2 pr-4">Waktu</th>
                        <th className="py-2 pr-4">Pengguna</th>
                        <th className="py-2 pr-4">Jenis</th>
                        <th className="py-2">Keterangan</th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                    {activities.map((activity) => (
                        <tr key={activity.id}>
                            <td className="whitespace-nowrap py-3 pr-4 text-slate-500">
                                {formatDateTime(activity.created_at)}
                            </td>
                            <td className="whitespace-nowrap py-3 pr-4 font-medium text-slate-900">
                                {activity.user?.name ?? '—'}
                            </td>
                            <td className="whitespace-nowrap py-3 pr-4">
                                <span
                                    className={
                                        'rounded-full px-2.5 py-0.5 text-xs font-medium ' +
                                        (ACTION_COLORS[activity.action] ?? 'bg-slate-100 text-slate-600')
                                    }
                                >
                                    {ACTION_LABELS[activity.action] ?? activity.action}
                                </span>
                            </td>
                            <td className="py-3 text-slate-600">{activity.description}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
