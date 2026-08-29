import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextareaInput from '@/Components/TextareaInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { STATUS_COLORS, STATUS_LABELS } from '@/Utils/complaintStatus';
import { Head, useForm, usePage } from '@inertiajs/react';

function formatDateTime(value) {
    return new Date(value).toLocaleString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

export default function Show({ complaint, nextTransition }) {
    const user = usePage().props.auth.user;
    const canAdvance = nextTransition && nextTransition.roles.includes(user.role);

    const { data, setData, patch, processing, errors, reset } = useForm({ note: '' });

    const submit = (e) => {
        e.preventDefault();
        patch(route('complaints.update-status', complaint.id), {
            onSuccess: () => reset(),
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Detail Pengaduan
                </h2>
            }
        >
            <Head title={complaint.title} />

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm lg:col-span-2">
                    <div className="flex items-start justify-between gap-4">
                        <div>
                            <h3 className="text-lg font-semibold text-gray-900">
                                {complaint.title}
                            </h3>
                            <p className="mt-1 text-sm text-gray-500">
                                Oleh {complaint.user?.name} · RT {complaint.rt?.nomor_rt}
                            </p>
                        </div>
                        <span
                            className={
                                'shrink-0 rounded px-2 py-0.5 text-xs font-medium ' +
                                STATUS_COLORS[complaint.status]
                            }
                        >
                            {STATUS_LABELS[complaint.status]}
                        </span>
                    </div>

                    <p className="mt-4 whitespace-pre-line text-sm text-gray-700">
                        {complaint.description}
                    </p>

                    {complaint.photo && (
                        <img
                            src={`/storage/${complaint.photo}`}
                            alt="Foto pendukung"
                            className="mt-4 max-h-80 rounded-md border border-gray-200 object-cover"
                        />
                    )}

                    {canAdvance && (
                        <form onSubmit={submit} className="mt-6 border-t border-gray-100 pt-4">
                            <InputLabel
                                htmlFor="note"
                                value={`Ubah status ke "${STATUS_LABELS[nextTransition.next]}"`}
                            />
                            <TextareaInput
                                id="note"
                                rows={2}
                                className="mt-1 block w-full"
                                placeholder="Catatan (opsional)"
                                value={data.note}
                                onChange={(e) => setData('note', e.target.value)}
                            />
                            <InputError message={errors.note} className="mt-1" />
                            <PrimaryButton className="mt-3" disabled={processing}>
                                Perbarui Status
                            </PrimaryButton>
                        </form>
                    )}
                </div>

                <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 className="text-sm font-semibold text-gray-900">Riwayat Status</h3>
                    <ol className="mt-4 space-y-4 border-l border-gray-200 pl-4">
                        {complaint.logs.map((log) => (
                            <li key={log.id} className="relative">
                                <span className="absolute -left-[21px] top-1 h-2.5 w-2.5 rounded-full bg-emerald-500" />
                                <p className="text-sm font-medium text-gray-900">
                                    {STATUS_LABELS[log.status]}
                                </p>
                                <p className="text-xs text-gray-500">
                                    {formatDateTime(log.created_at)} · {log.changed_by?.name}
                                </p>
                                {log.note && (
                                    <p className="mt-1 text-sm text-gray-600">{log.note}</p>
                                )}
                            </li>
                        ))}
                    </ol>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
