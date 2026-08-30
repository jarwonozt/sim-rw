import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Pagination from '@/Components/Pagination';
import PrimaryButton from '@/Components/PrimaryButton';
import SelectInput from '@/Components/SelectInput';
import TextareaInput from '@/Components/TextareaInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';

function formatDateTime(value) {
    return new Date(value).toLocaleString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

export default function Index({ rtOptions, templates, broadcasts }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        rt_id: '',
        message: '',
    });
    const [confirmSend, setConfirmSend] = useState(false);

    const applyTemplate = (e) => {
        const template = templates.find((t) => String(t.id) === e.target.value);
        if (template) {
            setData('message', template.content);
        }
    };

    const submit = (e) => {
        e.preventDefault();
        setConfirmSend(false);
        post(route('whatsapp-broadcast.store'), { onSuccess: () => reset('message') });
    };

    const targetLabel = data.rt_id
        ? `RT ${rtOptions.find((rt) => String(rt.id) === data.rt_id)?.nomor_rt ?? ''}`
        : 'semua RT';

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-bold tracking-tight text-slate-900">
                    Broadcast WhatsApp
                </h2>
            }
        >
            <Head title="Broadcast WhatsApp" />

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm shadow-slate-200/50 lg:col-span-2">
                    <h3 className="text-sm font-semibold text-slate-900">
                        Kirim Pesan Baru
                    </h3>
                    <p className="mt-1 text-xs text-slate-500">
                        Pesan dikirim ke nomor HP setiap penduduk yang datanya lengkap
                        pada target yang dipilih.
                    </p>

                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            setConfirmSend(true);
                        }}
                        className="mt-4 space-y-5"
                    >
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel htmlFor="rt_id" value="Target RT" />
                                <SelectInput
                                    id="rt_id"
                                    className="mt-1 block w-full"
                                    value={data.rt_id}
                                    onChange={(e) => setData('rt_id', e.target.value)}
                                >
                                    <option value="">Semua RT</option>
                                    {rtOptions.map((rt) => (
                                        <option key={rt.id} value={rt.id}>
                                            RT {rt.nomor_rt}
                                        </option>
                                    ))}
                                </SelectInput>
                                <InputError message={errors.rt_id} className="mt-1" />
                            </div>

                            {templates.length > 0 && (
                                <div>
                                    <InputLabel value="Mulai dari Template (opsional)" />
                                    <SelectInput
                                        className="mt-1 block w-full"
                                        defaultValue=""
                                        onChange={applyTemplate}
                                    >
                                        <option value="">— Tulis manual —</option>
                                        {templates.map((template) => (
                                            <option key={template.id} value={template.id}>
                                                {template.name}
                                            </option>
                                        ))}
                                    </SelectInput>
                                </div>
                            )}
                        </div>

                        <div>
                            <InputLabel htmlFor="message" value="Isi Pesan" />
                            <TextareaInput
                                id="message"
                                rows={5}
                                className="mt-1 block w-full"
                                value={data.message}
                                onChange={(e) => setData('message', e.target.value)}
                            />
                            <InputError message={errors.message} className="mt-1" />
                        </div>

                        <PrimaryButton disabled={processing || !data.message}>
                            Kirim Broadcast
                        </PrimaryButton>
                    </form>

                    {confirmSend && (
                        <div className="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                            <p>
                                Pesan akan dikirim ke seluruh penduduk dengan nomor HP di{' '}
                                <strong>{targetLabel}</strong>. Lanjutkan?
                            </p>
                            <div className="mt-3 flex gap-3">
                                <button
                                    type="button"
                                    onClick={submit}
                                    className="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-amber-700"
                                >
                                    Ya, Kirim Sekarang
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setConfirmSend(false)}
                                    className="rounded-lg border border-amber-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-amber-800 hover:bg-amber-100"
                                >
                                    Batal
                                </button>
                            </div>
                        </div>
                    )}
                </div>

                <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm shadow-slate-200/50">
                    <h3 className="text-sm font-semibold text-slate-900">Tips</h3>
                    <ul className="mt-3 list-inside list-disc space-y-2 text-sm text-slate-600">
                        <li>Cuma penduduk dengan No. HP terisi yang menerima pesan.</li>
                        <li>Nomor yang sama tidak akan menerima pesan dobel.</li>
                        <li>
                            Kelola pesan siap-pakai lewat menu Template Notifikasi
                            WhatsApp.
                        </li>
                    </ul>
                </div>
            </div>

            <div className="mt-6 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm shadow-slate-200/50">
                <h3 className="text-sm font-semibold text-slate-900">Riwayat Broadcast</h3>

                <div className="mt-4 overflow-hidden rounded-xl border border-slate-100">
                    <table className="min-w-full divide-y divide-slate-100 text-sm">
                        <thead className="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-400">
                            <tr>
                                <th className="px-4 py-3">Waktu</th>
                                <th className="px-4 py-3">Pengirim</th>
                                <th className="px-4 py-3">Target</th>
                                <th className="px-4 py-3">Pesan</th>
                                <th className="px-4 py-3">Hasil</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {broadcasts.data.map((broadcast) => (
                                <tr key={broadcast.id}>
                                    <td className="whitespace-nowrap px-4 py-3 text-slate-500">
                                        {formatDateTime(broadcast.created_at)}
                                    </td>
                                    <td className="whitespace-nowrap px-4 py-3 font-medium text-slate-900">
                                        {broadcast.sender?.name}
                                    </td>
                                    <td className="whitespace-nowrap px-4 py-3 text-slate-600">
                                        {broadcast.rt ? `RT ${broadcast.rt.nomor_rt}` : 'Semua RT'}
                                    </td>
                                    <td className="max-w-xs truncate px-4 py-3 text-slate-600">
                                        {broadcast.message}
                                    </td>
                                    <td className="whitespace-nowrap px-4 py-3 text-slate-600">
                                        <span className="text-emerald-700">
                                            {broadcast.success_count} berhasil
                                        </span>
                                        {broadcast.failed_count > 0 && (
                                            <span className="text-rose-600">
                                                {' '}
                                                · {broadcast.failed_count} gagal
                                            </span>
                                        )}
                                    </td>
                                </tr>
                            ))}
                            {broadcasts.data.length === 0 && (
                                <tr>
                                    <td colSpan={5} className="px-4 py-6 text-center text-slate-500">
                                        Belum ada broadcast yang dikirim.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <div className="mt-4">
                    <Pagination links={broadcasts.links} />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
