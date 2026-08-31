import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

const LOGIN_EXAMPLE = `curl -X POST https://domain-anda.tld/api/v1/login \\
  -H "Accept: application/json" \\
  -H "Content-Type: application/json" \\
  -d '{"email": "warga@example.com", "password": "rahasia"}'`;

const AUTHENTICATED_EXAMPLE = `curl https://domain-anda.tld/api/v1/dashboard \\
  -H "Accept: application/json" \\
  -H "Authorization: Bearer <token>"`;

export default function Index({ groups }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">Panduan API</h2>
            }
        >
            <Head title="Panduan API" />

            <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 className="text-sm font-semibold text-gray-900">REST API SIM-RW v1</h3>
                <p className="mt-2 text-sm text-gray-600">
                    Fondasi API untuk aplikasi mobile warga dan integrasi eksternal (lihat{' '}
                    <code className="text-gray-800">docs/issues/002-rest-api.md</code> dan{' '}
                    <code className="text-gray-800">docs/api-guide.md</code> di repository).
                    Setiap endpoint memakai token barrier (Laravel Sanctum) — tidak ada endpoint
                    yang bisa diakses tanpa token, termasuk yang bersifat &ldquo;lihat
                    saja&rdquo; seperti pengumuman.
                </p>

                <div className="mt-4 flex flex-wrap gap-3">
                    <a
                        href="/docs/api"
                        target="_blank"
                        rel="noreferrer"
                        className="inline-flex items-center rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white shadow-md shadow-primary/30 hover:opacity-90"
                    >
                        Buka Dokumentasi Interaktif
                    </a>
                    <a
                        href="/docs/api.json"
                        target="_blank"
                        rel="noreferrer"
                        className="inline-flex items-center rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Unduh Spesifikasi OpenAPI (JSON)
                    </a>
                </div>
                <p className="mt-2 text-xs text-gray-500">
                    Dokumentasi interaktif dibuat otomatis oleh Scramble dari kode sumber
                    (route, Form Request, dan API Resource) sehingga selalu sinkron dengan
                    endpoint yang benar-benar berjalan.
                </p>
            </div>

            <div className="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 className="text-sm font-semibold text-gray-900">
                        1. Login untuk mendapatkan token
                    </h3>
                    <pre className="mt-3 overflow-x-auto rounded-lg bg-gray-900 p-4 text-xs text-gray-100">
                        {LOGIN_EXAMPLE}
                    </pre>
                    <p className="mt-3 text-sm text-gray-600">
                        Respons berisi <code className="text-gray-800">data.token</code>. Simpan
                        token ini di sisi klien (mis. secure storage pada aplikasi mobile).
                        Percobaan login dibatasi 5 kali/menit per kombinasi email+IP.
                    </p>
                </div>

                <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 className="text-sm font-semibold text-gray-900">
                        2. Kirim token di setiap request lain
                    </h3>
                    <pre className="mt-3 overflow-x-auto rounded-lg bg-gray-900 p-4 text-xs text-gray-100">
                        {AUTHENTICATED_EXAMPLE}
                    </pre>
                    <p className="mt-3 text-sm text-gray-600">
                        Token tidak kedaluwarsa otomatis — panggil{' '}
                        <code className="text-gray-800">POST /api/v1/logout</code> untuk
                        mencabutnya (mis. saat pengguna keluar dari aplikasi).
                    </p>
                </div>
            </div>

            <div className="mt-6 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 className="text-sm font-semibold text-gray-900">Format Respons & Error</h3>
                <ul className="mt-3 list-disc space-y-1 pl-5 text-sm text-gray-600">
                    <li>
                        Sukses: <code className="text-gray-800">{'{ "data": ... }'}</code>,
                        daftar berpaginasi menambahkan{' '}
                        <code className="text-gray-800">meta</code> &amp;{' '}
                        <code className="text-gray-800">links</code>.
                    </li>
                    <li>
                        Validasi gagal (422):{' '}
                        <code className="text-gray-800">
                            {'{ "message": ..., "errors": { "field": [...] } }'}
                        </code>
                    </li>
                    <li>
                        Tidak punya akses (403) atau token tidak valid/hilang (401):{' '}
                        <code className="text-gray-800">{'{ "message": ... }'}</code>
                    </li>
                </ul>
            </div>

            <div className="mt-6 space-y-6">
                {groups.map((group) => (
                    <div
                        key={group.name}
                        className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm"
                    >
                        <div className="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 px-6 py-4">
                            <h3 className="text-sm font-semibold text-gray-900">{group.name}</h3>
                            <span className="text-xs text-gray-500">{group.roles}</span>
                        </div>
                        <table className="min-w-full divide-y divide-gray-100 text-sm">
                            <tbody className="divide-y divide-gray-100">
                                {group.endpoints.map((endpoint) => (
                                    <tr key={endpoint.method + endpoint.path}>
                                        <td className="w-40 px-6 py-3 align-top">
                                            <span className="inline-flex rounded-md bg-gray-100 px-2 py-0.5 font-mono text-xs font-semibold text-gray-700">
                                                {endpoint.method}
                                            </span>
                                        </td>
                                        <td className="px-2 py-3 align-top font-mono text-xs text-gray-800">
                                            {group.base}
                                            {endpoint.path}
                                        </td>
                                        <td className="px-6 py-3 align-top text-gray-600">
                                            {endpoint.desc}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                ))}
            </div>
        </AuthenticatedLayout>
    );
}
