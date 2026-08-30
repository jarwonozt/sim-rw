import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import SelectInput from '@/Components/SelectInput';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';

function valuesFromRt(rt) {
    return {
        nomor_rt: rt?.nomor_rt ?? '',
        ketua_rt_id: rt?.ketua_rt_id ?? '',
    };
}

function RtFormModal({ show, onClose, rt, ketuaRtOptions }) {
    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm(
        valuesFromRt(rt),
    );

    // Modal ini tetap ter-mount di belakang layar (cuma `show` yang berubah),
    // jadi perlu di-resync manual tiap kali RT yang diedit berganti.
    useEffect(() => {
        setData(valuesFromRt(rt));
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [rt]);

    const isEditing = Boolean(rt);

    const submit = (e) => {
        e.preventDefault();

        const options = { onSuccess: () => close() };

        if (isEditing) {
            put(route('rt.update', rt.id), options);
        } else {
            post(route('rt.store'), options);
        }
    };

    const close = () => {
        reset();
        clearErrors();
        onClose();
    };

    return (
        <Modal show={show} onClose={close} maxWidth="md">
            <form onSubmit={submit} className="p-6">
                <h2 className="text-lg font-semibold text-gray-900">
                    {isEditing ? 'Edit RT' : 'Tambah RT'}
                </h2>

                <div className="mt-4">
                    <InputLabel htmlFor="nomor_rt" value="Nomor RT" />
                    <TextInput
                        id="nomor_rt"
                        className="mt-1 block w-full"
                        value={data.nomor_rt}
                        onChange={(e) => setData('nomor_rt', e.target.value)}
                        isFocused
                    />
                    <InputError message={errors.nomor_rt} className="mt-1" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="ketua_rt_id" value="Ketua RT" />
                    <SelectInput
                        id="ketua_rt_id"
                        className="mt-1 block w-full"
                        value={data.ketua_rt_id}
                        onChange={(e) => setData('ketua_rt_id', e.target.value)}
                    >
                        <option value="">— Belum ditentukan —</option>
                        {ketuaRtOptions.map((u) => (
                            <option key={u.id} value={u.id}>
                                {u.name}
                            </option>
                        ))}
                    </SelectInput>
                    <InputError message={errors.ketua_rt_id} className="mt-1" />
                </div>

                <div className="mt-6 flex justify-end gap-3">
                    <SecondaryButton onClick={close}>Batal</SecondaryButton>
                    <PrimaryButton disabled={processing}>Simpan</PrimaryButton>
                </div>
            </form>
        </Modal>
    );
}

export default function Index({ rts, ketuaRtOptions }) {
    const [formState, setFormState] = useState({ show: false, rt: null });
    const [confirmDelete, setConfirmDelete] = useState(null);

    const destroy = () => {
        router.delete(route('rt.destroy', confirmDelete.id), {
            onFinish: () => setConfirmDelete(null),
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Data RT
                </h2>
            }
        >
            <Head title="Data RT" />

            <div className="mb-4 flex justify-end">
                <PrimaryButton onClick={() => setFormState({ show: true, rt: null })}>
                    + Tambah RT
                </PrimaryButton>
            </div>

            <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <table className="min-w-full divide-y divide-gray-100 text-sm">
                    <thead className="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                        <tr>
                            <th className="px-4 py-3">RT</th>
                            <th className="px-4 py-3">Ketua RT</th>
                            <th className="px-4 py-3">Jumlah KK</th>
                            <th className="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {rts.map((rt) => (
                            <tr key={rt.id}>
                                <td className="px-4 py-3 font-medium text-gray-900">
                                    RT {rt.nomor_rt}
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {rt.ketua_rt?.name ?? '—'}
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {rt.family_heads_count}
                                </td>
                                <td className="px-4 py-3 text-right">
                                    <button
                                        onClick={() => setFormState({ show: true, rt })}
                                        className="mr-3 text-sm font-medium text-emerald-700 hover:underline"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        onClick={() => setConfirmDelete(rt)}
                                        className="text-sm font-medium text-rose-600 hover:underline"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        ))}
                        {rts.length === 0 && (
                            <tr>
                                <td colSpan={4} className="px-4 py-6 text-center text-gray-500">
                                    Belum ada data RT.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <RtFormModal
                show={formState.show}
                rt={formState.rt}
                ketuaRtOptions={ketuaRtOptions}
                onClose={() => setFormState({ show: false, rt: null })}
            />

            <Modal show={Boolean(confirmDelete)} onClose={() => setConfirmDelete(null)} maxWidth="md">
                <div className="p-6">
                    <h2 className="text-lg font-semibold text-gray-900">Hapus RT?</h2>
                    <p className="mt-2 text-sm text-gray-600">
                        RT {confirmDelete?.nomor_rt} akan dihapus permanen. Tindakan ini
                        tidak bisa dibatalkan.
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
