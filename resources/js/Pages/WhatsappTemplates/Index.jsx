import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import SelectInput from '@/Components/SelectInput';
import TextareaInput from '@/Components/TextareaInput';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';

function valuesFromTemplate(template) {
    return {
        name: template?.name ?? '',
        event_key: template?.event_key ?? '',
        content: template?.content ?? '',
        is_active: template?.is_active ?? true,
    };
}

function TemplateFormModal({ show, onClose, template, events, broadcastPlaceholders }) {
    const isEditing = Boolean(template);

    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm(
        valuesFromTemplate(template),
    );

    // Modal ini tetap ter-mount di belakang layar (cuma `show` yang berubah),
    // jadi perlu di-resync manual tiap kali template yang diedit berganti.
    useEffect(() => {
        setData(valuesFromTemplate(template));
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [template]);

    const close = () => {
        reset();
        clearErrors();
        onClose();
    };

    const submit = (e) => {
        e.preventDefault();
        const options = { onSuccess: () => close() };

        if (isEditing) {
            put(route('whatsapp-templates.update', template.id), options);
        } else {
            post(route('whatsapp-templates.store'), options);
        }
    };

    const selectedEvent = events.find((event) => event.key === data.event_key);
    const placeholders = selectedEvent ? selectedEvent.placeholders : broadcastPlaceholders;

    return (
        <Modal show={show} onClose={close} maxWidth="lg">
            <form onSubmit={submit} className="p-6">
                <h2 className="text-lg font-semibold text-gray-900">
                    {isEditing ? 'Edit Template WhatsApp' : 'Tambah Template WhatsApp'}
                </h2>

                <div className="mt-4">
                    <InputLabel htmlFor="name" value="Nama Template" />
                    <TextInput
                        id="name"
                        className="mt-1 block w-full"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        isFocused
                    />
                    <InputError message={errors.name} className="mt-1" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="event_key" value="Dipakai Otomatis Untuk" />
                    <SelectInput
                        id="event_key"
                        className="mt-1 block w-full"
                        value={data.event_key}
                        onChange={(e) => setData('event_key', e.target.value)}
                    >
                        <option value="">— Cuma untuk broadcast manual —</option>
                        {events.map((event) => (
                            <option key={event.key} value={event.key}>
                                {event.label}
                            </option>
                        ))}
                    </SelectInput>
                    <p className="mt-1 text-xs text-gray-500">
                        Kalau dipilih, template ini otomatis dipakai sistem saat kejadian
                        itu terjadi (menggantikan pesan bawaan).
                    </p>
                    <InputError message={errors.event_key} className="mt-1" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="content" value="Isi Pesan" />
                    <p className="mt-1 text-xs text-gray-500">
                        Placeholder yang tersedia:{' '}
                        {(placeholders ?? []).map((p) => (
                            <code
                                key={p}
                                className="mr-1 rounded bg-gray-100 px-1 py-0.5 text-gray-700"
                            >
                                [{p}]
                            </code>
                        ))}
                    </p>
                    <TextareaInput
                        id="content"
                        rows={5}
                        className="mt-2 block w-full"
                        value={data.content}
                        onChange={(e) => setData('content', e.target.value)}
                    />
                    <InputError message={errors.content} className="mt-1" />
                </div>

                <label className="mt-4 flex items-center gap-2 text-sm text-gray-700">
                    <input
                        type="checkbox"
                        className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        checked={data.is_active}
                        onChange={(e) => setData('is_active', e.target.checked)}
                    />
                    Aktif
                </label>

                <div className="mt-6 flex justify-end gap-3">
                    <SecondaryButton onClick={close}>Batal</SecondaryButton>
                    <PrimaryButton disabled={processing}>Simpan</PrimaryButton>
                </div>
            </form>
        </Modal>
    );
}

export default function Index({ templates, events, broadcastPlaceholders }) {
    const [formState, setFormState] = useState({ show: false, template: null });
    const [confirmDelete, setConfirmDelete] = useState(null);

    const destroy = () => {
        router.delete(route('whatsapp-templates.destroy', confirmDelete.id), {
            onFinish: () => setConfirmDelete(null),
        });
    };

    const eventLabel = (key) => events.find((event) => event.key === key)?.label;

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Template Notifikasi WhatsApp
                </h2>
            }
        >
            <Head title="Template WhatsApp" />

            <div className="mb-4 flex justify-end">
                <PrimaryButton onClick={() => setFormState({ show: true, template: null })}>
                    + Tambah Template
                </PrimaryButton>
            </div>

            <div className="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm shadow-slate-200/50">
                <table className="min-w-full divide-y divide-gray-100 text-sm">
                    <thead className="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                        <tr>
                            <th className="px-4 py-3">Nama</th>
                            <th className="px-4 py-3">Dipakai Otomatis Untuk</th>
                            <th className="px-4 py-3">Status</th>
                            <th className="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {templates.map((template) => (
                            <tr key={template.id}>
                                <td className="px-4 py-3 font-medium text-gray-900">
                                    {template.name}
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {eventLabel(template.event_key) ?? (
                                        <span className="text-gray-400">
                                            Broadcast manual
                                        </span>
                                    )}
                                </td>
                                <td className="px-4 py-3">
                                    <span
                                        className={
                                            'rounded px-2 py-0.5 text-xs font-medium ' +
                                            (template.is_active
                                                ? 'bg-emerald-50 text-emerald-700'
                                                : 'bg-gray-100 text-gray-500')
                                        }
                                    >
                                        {template.is_active ? 'Aktif' : 'Nonaktif'}
                                    </span>
                                </td>
                                <td className="px-4 py-3 text-right">
                                    <button
                                        onClick={() => setFormState({ show: true, template })}
                                        className="mr-3 text-sm font-medium text-primary hover:underline"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        onClick={() => setConfirmDelete(template)}
                                        className="text-sm font-medium text-rose-600 hover:underline"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        ))}
                        {templates.length === 0 && (
                            <tr>
                                <td colSpan={4} className="px-4 py-6 text-center text-gray-500">
                                    Belum ada template WhatsApp.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <TemplateFormModal
                show={formState.show}
                template={formState.template}
                events={events}
                broadcastPlaceholders={broadcastPlaceholders}
                onClose={() => setFormState({ show: false, template: null })}
            />

            <Modal show={Boolean(confirmDelete)} onClose={() => setConfirmDelete(null)} maxWidth="md">
                <div className="p-6">
                    <h2 className="text-lg font-semibold text-gray-900">Hapus template?</h2>
                    <p className="mt-2 text-sm text-gray-600">
                        Template "{confirmDelete?.name}" akan dihapus permanen.
                    </p>
                    <div className="mt-6 flex justify-end gap-3">
                        <SecondaryButton onClick={() => setConfirmDelete(null)}>
                            Batal
                        </SecondaryButton>
                        <DangerButton onClick={destroy}>Hapus</DangerButton>
                    </div>
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}
