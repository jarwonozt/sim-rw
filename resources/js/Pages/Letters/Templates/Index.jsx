import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextareaInput from '@/Components/TextareaInput';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';

function TemplateFormModal({ show, onClose, template, placeholders }) {
    const isEditing = Boolean(template);

    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm({
        name: template?.name ?? '',
        type: template?.type ?? '',
        content: template?.content ?? '',
        is_active: template?.is_active ?? true,
    });

    const close = () => {
        reset();
        clearErrors();
        onClose();
    };

    const submit = (e) => {
        e.preventDefault();
        const options = { onSuccess: () => close() };

        if (isEditing) {
            put(route('letter-templates.update', template.id), options);
        } else {
            post(route('letter-templates.store'), options);
        }
    };

    return (
        <Modal show={show} onClose={close} maxWidth="2xl">
            <form onSubmit={submit} className="max-h-[85vh] overflow-y-auto p-6">
                <h2 className="text-lg font-semibold text-gray-900">
                    {isEditing ? 'Edit Template Surat' : 'Tambah Template Surat'}
                </h2>

                <div className="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
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

                    <div>
                        <InputLabel htmlFor="type" value="Jenis Surat" />
                        <TextInput
                            id="type"
                            className="mt-1 block w-full"
                            placeholder="domisili / sktm / usaha"
                            value={data.type}
                            onChange={(e) => setData('type', e.target.value)}
                        />
                        <InputError message={errors.type} className="mt-1" />
                    </div>
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="content" value="Isi Surat" />
                    <p className="mt-1 text-xs text-gray-500">
                        Placeholder yang tersedia:{' '}
                        {placeholders.map((p) => (
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
                        rows={8}
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
                    Aktif (bisa dipakai menerbitkan surat)
                </label>

                <div className="mt-6 flex justify-end gap-3">
                    <SecondaryButton onClick={close}>Batal</SecondaryButton>
                    <PrimaryButton disabled={processing}>Simpan</PrimaryButton>
                </div>
            </form>
        </Modal>
    );
}

export default function Index({ templates, placeholders }) {
    const [formState, setFormState] = useState({ show: false, template: null });
    const [confirmDelete, setConfirmDelete] = useState(null);

    const destroy = () => {
        router.delete(route('letter-templates.destroy', confirmDelete.id), {
            onFinish: () => setConfirmDelete(null),
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Template Surat
                </h2>
            }
        >
            <Head title="Template Surat" />

            <div className="mb-4 flex justify-end">
                <PrimaryButton onClick={() => setFormState({ show: true, template: null })}>
                    + Tambah Template
                </PrimaryButton>
            </div>

            <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <table className="min-w-full divide-y divide-gray-100 text-sm">
                    <thead className="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                        <tr>
                            <th className="px-4 py-3">Nama</th>
                            <th className="px-4 py-3">Jenis</th>
                            <th className="px-4 py-3">Status</th>
                            <th className="px-4 py-3">Terpakai</th>
                            <th className="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {templates.map((template) => (
                            <tr key={template.id}>
                                <td className="px-4 py-3 font-medium text-gray-900">
                                    {template.name}
                                </td>
                                <td className="px-4 py-3 text-gray-600">{template.type}</td>
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
                                <td className="px-4 py-3 text-gray-600">
                                    {template.letters_count} surat
                                </td>
                                <td className="px-4 py-3 text-right">
                                    <button
                                        onClick={() => setFormState({ show: true, template })}
                                        className="mr-3 text-sm font-medium text-emerald-700 hover:underline"
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
                                <td colSpan={5} className="px-4 py-6 text-center text-gray-500">
                                    Belum ada template surat.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <TemplateFormModal
                show={formState.show}
                template={formState.template}
                placeholders={placeholders}
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
