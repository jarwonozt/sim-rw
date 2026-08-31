import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import Pagination from '@/Components/Pagination';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import SelectInput from '@/Components/SelectInput';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';

const ROLE_LABELS = {
    super_admin: 'Super Admin',
    ketua_rw: 'Ketua RW',
    sekretaris: 'Sekretaris',
    bendahara: 'Bendahara',
    ketua_rt: 'Ketua RT',
    warga: 'Warga',
};

function formatDateTime(value) {
    if (!value) {
        return null;
    }

    return new Date(value).toLocaleString('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}

function CreateUserModal({ show, onClose, roles }) {
    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'warga',
    });

    const close = () => {
        reset();
        clearErrors();
        onClose();
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('api-access.users.store'), { onSuccess: () => close() });
    };

    return (
        <Modal show={show} onClose={close} maxWidth="md">
            <form onSubmit={submit} className="p-6">
                <h2 className="text-lg font-semibold text-gray-900">Tambah Akun untuk Akses API</h2>
                <p className="mt-1 text-sm text-gray-600">
                    Akun ini bisa dipakai login ke dashboard web maupun untuk menerbitkan
                    token API — hak aksesnya mengikuti peran yang dipilih di bawah.
                </p>

                <div className="mt-4 space-y-4">
                    <div>
                        <InputLabel htmlFor="name" value="Nama" />
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
                        <InputLabel htmlFor="email" value="Email" />
                        <TextInput
                            id="email"
                            type="email"
                            className="mt-1 block w-full"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                        />
                        <InputError message={errors.email} className="mt-1" />
                    </div>

                    <div>
                        <InputLabel htmlFor="role" value="Peran" />
                        <SelectInput
                            id="role"
                            className="mt-1 block w-full"
                            value={data.role}
                            onChange={(e) => setData('role', e.target.value)}
                        >
                            {roles.map((role) => (
                                <option key={role} value={role}>
                                    {ROLE_LABELS[role] ?? role}
                                </option>
                            ))}
                        </SelectInput>
                        <InputError message={errors.role} className="mt-1" />
                    </div>

                    <div>
                        <InputLabel htmlFor="password" value="Password" />
                        <TextInput
                            id="password"
                            type="password"
                            className="mt-1 block w-full"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                        />
                        <InputError message={errors.password} className="mt-1" />
                    </div>

                    <div>
                        <InputLabel htmlFor="password_confirmation" value="Konfirmasi Password" />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            className="mt-1 block w-full"
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                        />
                    </div>
                </div>

                <div className="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" onClick={close}>
                        Batal
                    </SecondaryButton>
                    <PrimaryButton disabled={processing}>Buat Akun</PrimaryButton>
                </div>
            </form>
        </Modal>
    );
}

function TokenPanel({ selectedUser, selectedUserTokens, newApiToken, search }) {
    const [confirmRevoke, setConfirmRevoke] = useState(null);
    const [copied, setCopied] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({ name: '' });

    if (!selectedUser) {
        return (
            <div className="flex h-full items-center justify-center rounded-xl border border-dashed border-gray-200 p-8 text-center text-sm text-gray-500">
                Pilih salah satu akun di tabel untuk mengelola token API-nya.
            </div>
        );
    }

    const submit = (e) => {
        e.preventDefault();
        post(route('api-access.tokens.store', selectedUser.id), { onSuccess: () => reset() });
    };

    const revoke = () => {
        router.delete(route('api-access.tokens.destroy', confirmRevoke.id), {
            onFinish: () => setConfirmRevoke(null),
        });
    };

    const copyToken = async () => {
        try {
            await navigator.clipboard.writeText(newApiToken);
            setCopied(true);
        } catch {
            setCopied(false);
        }
    };

    return (
        <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <h3 className="text-sm font-semibold text-gray-900">
                Token API — {selectedUser.name}
            </h3>
            <p className="mt-1 text-xs text-gray-500">
                {selectedUser.email} · {ROLE_LABELS[selectedUser.role] ?? selectedUser.role}
            </p>

            {newApiToken && (
                <div className="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4">
                    <p className="text-sm font-medium text-amber-900">
                        Token baru dibuat — salin sekarang, token ini tidak akan ditampilkan
                        lagi.
                    </p>
                    <div className="mt-2 flex flex-wrap items-center gap-2">
                        <code className="break-all rounded-md bg-white px-3 py-2 text-xs text-gray-800">
                            {newApiToken}
                        </code>
                        <SecondaryButton type="button" onClick={copyToken}>
                            {copied ? 'Tersalin!' : 'Salin'}
                        </SecondaryButton>
                    </div>
                </div>
            )}

            <form onSubmit={submit} className="mt-4 flex flex-wrap items-end gap-3">
                <div className="flex-1">
                    <InputLabel htmlFor="token_name" value="Nama Token" />
                    <TextInput
                        id="token_name"
                        className="mt-1 block w-full"
                        placeholder="mis. Integrasi Server Kelurahan"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                    />
                    <InputError message={errors.name} className="mt-1" />
                </div>
                <PrimaryButton disabled={processing}>Buat Token</PrimaryButton>
            </form>

            <div className="mt-6 overflow-hidden rounded-lg border border-gray-100">
                <table className="min-w-full divide-y divide-gray-100 text-sm">
                    <thead className="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                        <tr>
                            <th className="px-4 py-3">Nama</th>
                            <th className="px-4 py-3">Dibuat</th>
                            <th className="px-4 py-3">Terakhir Dipakai</th>
                            <th className="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {selectedUserTokens.map((token) => (
                            <tr key={token.id}>
                                <td className="px-4 py-3 font-medium text-gray-900">
                                    {token.name}
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {formatDateTime(token.created_at)}
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {formatDateTime(token.last_used_at) ?? 'Belum pernah dipakai'}
                                </td>
                                <td className="px-4 py-3 text-right">
                                    <button
                                        type="button"
                                        onClick={() => setConfirmRevoke(token)}
                                        className="text-sm font-medium text-rose-600 hover:underline"
                                    >
                                        Cabut
                                    </button>
                                </td>
                            </tr>
                        ))}
                        {selectedUserTokens.length === 0 && (
                            <tr>
                                <td colSpan={4} className="px-4 py-6 text-center text-gray-500">
                                    Akun ini belum punya token.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <Modal show={Boolean(confirmRevoke)} onClose={() => setConfirmRevoke(null)} maxWidth="md">
                <div className="p-6">
                    <h2 className="text-lg font-semibold text-gray-900">Cabut token?</h2>
                    <p className="mt-2 text-sm text-gray-600">
                        Token "{confirmRevoke?.name}" tidak akan bisa dipakai lagi untuk
                        mengakses API setelah dicabut.
                    </p>
                    <div className="mt-6 flex justify-end gap-3">
                        <SecondaryButton onClick={() => setConfirmRevoke(null)}>
                            Batal
                        </SecondaryButton>
                        <DangerButton onClick={revoke}>Cabut</DangerButton>
                    </div>
                </div>
            </Modal>
        </div>
    );
}

export default function Index({ users, filters, roles, selectedUser, selectedUserTokens, newApiToken }) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [showCreate, setShowCreate] = useState(false);

    const submitSearch = (e) => {
        e.preventDefault();
        router.get(route('api-access.index'), { search }, { preserveState: true });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Manajemen Akses API
                </h2>
            }
        >
            <Head title="Manajemen Akses API" />

            <p className="mb-4 text-sm text-gray-600">
                Tambahkan akun untuk developer/integrasi (baru atau yang sudah ada), lalu
                terbitkan token API untuk akun tersebut. Lihat{' '}
                <code className="text-gray-800">docs/api-guide.md</code> untuk cara memakai
                tokennya.
            </p>

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-5">
                <div className="lg:col-span-3">
                    <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <form onSubmit={submitSearch} className="flex gap-2">
                            <TextInput
                                placeholder="Cari nama atau email..."
                                className="w-64"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                            <PrimaryButton type="submit">Cari</PrimaryButton>
                        </form>

                        <PrimaryButton onClick={() => setShowCreate(true)}>
                            + Tambah Akun
                        </PrimaryButton>
                    </div>

                    <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                        <table className="min-w-full divide-y divide-gray-100 text-sm">
                            <thead className="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th className="px-4 py-3">Nama</th>
                                    <th className="px-4 py-3">Peran</th>
                                    <th className="px-4 py-3">Token</th>
                                    <th className="px-4 py-3" />
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {users.data.map((user) => (
                                    <tr
                                        key={user.id}
                                        className={
                                            selectedUser?.id === user.id ? 'bg-primary/5' : undefined
                                        }
                                    >
                                        <td className="px-4 py-3">
                                            <p className="font-medium text-gray-900">
                                                {user.name}
                                            </p>
                                            <p className="text-xs text-gray-500">{user.email}</p>
                                        </td>
                                        <td className="px-4 py-3 text-gray-600">
                                            {ROLE_LABELS[user.role] ?? user.role}
                                        </td>
                                        <td className="px-4 py-3 text-gray-600">
                                            {user.tokens_count}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <Link
                                                href={route('api-access.index', {
                                                    user_id: user.id,
                                                    search,
                                                })}
                                                className="text-sm font-medium text-primary hover:underline"
                                            >
                                                Kelola Token
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                                {users.data.length === 0 && (
                                    <tr>
                                        <td colSpan={4} className="px-4 py-6 text-center text-gray-500">
                                            Tidak ada akun ditemukan.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    <div className="mt-4">
                        <Pagination links={users.links} />
                    </div>
                </div>

                <div className="lg:col-span-2">
                    <TokenPanel
                        selectedUser={selectedUser}
                        selectedUserTokens={selectedUserTokens}
                        newApiToken={newApiToken}
                        search={search}
                    />
                </div>
            </div>

            <CreateUserModal show={showCreate} onClose={() => setShowCreate(false)} roles={roles} />
        </AuthenticatedLayout>
    );
}
