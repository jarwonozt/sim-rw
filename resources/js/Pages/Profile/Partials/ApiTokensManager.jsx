import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import { router, useForm } from '@inertiajs/react';
import { useState } from 'react';

function formatDateTime(value) {
    if (!value) {
        return null;
    }

    return new Date(value).toLocaleString('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}

export default function ApiTokensManager({ tokens, newApiToken, className = '' }) {
    const [confirmRevoke, setConfirmRevoke] = useState(null);
    const [copied, setCopied] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({ name: '' });

    const submit = (e) => {
        e.preventDefault();
        post(route('api-tokens.store'), { onSuccess: () => reset() });
    };

    const revoke = () => {
        router.delete(route('api-tokens.destroy', confirmRevoke.id), {
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
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-gray-900">Token API</h2>

                <p className="mt-1 text-sm text-gray-600">
                    Buat token pribadi untuk memakai REST API SIM-RW (
                    <code className="text-gray-800">docs/api-guide.md</code>) sebagai akun
                    ini — cocok untuk pengujian developer maupun integrasi produksi
                    (skrip/server yang login sebagai akun ini) tanpa memanggil{' '}
                    <code className="text-gray-800">POST /api/v1/login</code> berulang kali.
                </p>
            </header>

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

            <form onSubmit={submit} className="mt-6 flex flex-wrap items-end gap-4">
                <div className="flex-1">
                    <InputLabel htmlFor="token_name" value="Nama Token" />
                    <TextInput
                        id="token_name"
                        className="mt-1 block w-full"
                        placeholder="mis. Aplikasi Mobile Android, Server Integrasi"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                    />
                    <InputError message={errors.name} className="mt-2" />
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
                        {tokens.map((token) => (
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
                        {tokens.length === 0 && (
                            <tr>
                                <td colSpan={4} className="px-4 py-6 text-center text-gray-500">
                                    Belum ada token.
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
        </section>
    );
}
