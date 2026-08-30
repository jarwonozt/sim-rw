import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

function formatDate(value) {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

function ReadOnlyField({ label, value }) {
    return (
        <div>
            <p className="text-xs font-medium uppercase tracking-wide text-slate-400">
                {label}
            </p>
            <p className="mt-0.5 text-sm text-slate-900">{value || '—'}</p>
        </div>
    );
}

export default function Edit({ resident }) {
    const { data, setData, put, processing, errors } = useForm({
        occupation: resident?.occupation ?? '',
        education: resident?.education ?? '',
        religion: resident?.religion ?? '',
        marital_status: resident?.marital_status ?? '',
        phone: resident?.phone ?? '',
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('resident-profile.update'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-bold tracking-tight text-slate-900">
                    Data Saya
                </h2>
            }
        >
            <Head title="Data Saya" />

            {!resident ? (
                <div className="rounded-2xl border border-slate-100 bg-white p-6 text-sm text-slate-600 shadow-sm shadow-slate-200/50">
                    Akun Anda belum terhubung dengan data Penduduk. Hubungi Sekretaris
                    atau Ketua RT setempat untuk menghubungkan akun Anda.
                </div>
            ) : (
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm shadow-slate-200/50 lg:col-span-1">
                        <h3 className="text-sm font-semibold text-slate-900">
                            Data Identitas
                        </h3>
                        <p className="mt-1 text-xs text-slate-500">
                            Hanya bisa diubah pengurus RW. Hubungi Sekretaris/Ketua RT
                            bila ada kesalahan.
                        </p>

                        <div className="mt-4 space-y-3">
                            <ReadOnlyField label="NIK" value={resident.nik} />
                            <ReadOnlyField label="Nama" value={resident.name} />
                            <ReadOnlyField
                                label="Jenis Kelamin"
                                value={resident.gender === 'L' ? 'Laki-laki' : 'Perempuan'}
                            />
                            <ReadOnlyField
                                label="Tempat, Tanggal Lahir"
                                value={`${resident.birth_place ?? '—'}, ${formatDate(resident.birth_date)}`}
                            />
                            <ReadOnlyField
                                label="Hubungan dalam Keluarga"
                                value={resident.relationship_status}
                            />
                            <ReadOnlyField
                                label="No. KK"
                                value={resident.family_head?.no_kk}
                            />
                            <ReadOnlyField
                                label="RT"
                                value={
                                    resident.family_head?.rt?.nomor_rt
                                        ? `RT ${resident.family_head.rt.nomor_rt}`
                                        : null
                                }
                            />
                        </div>
                    </div>

                    <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm shadow-slate-200/50 lg:col-span-2">
                        <h3 className="text-sm font-semibold text-slate-900">
                            Data Kontak &amp; Lainnya
                        </h3>
                        <p className="mt-1 text-xs text-slate-500">
                            Bagian ini boleh Anda perbarui sendiri.
                        </p>

                        <form onSubmit={submit} className="mt-4 space-y-5">
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <InputLabel htmlFor="phone" value="No. HP" />
                                    <TextInput
                                        id="phone"
                                        className="mt-1 block w-full"
                                        value={data.phone}
                                        onChange={(e) => setData('phone', e.target.value)}
                                    />
                                    <InputError message={errors.phone} className="mt-1" />
                                </div>

                                <div>
                                    <InputLabel htmlFor="occupation" value="Pekerjaan" />
                                    <TextInput
                                        id="occupation"
                                        className="mt-1 block w-full"
                                        value={data.occupation}
                                        onChange={(e) => setData('occupation', e.target.value)}
                                    />
                                    <InputError message={errors.occupation} className="mt-1" />
                                </div>

                                <div>
                                    <InputLabel htmlFor="education" value="Pendidikan" />
                                    <TextInput
                                        id="education"
                                        className="mt-1 block w-full"
                                        value={data.education}
                                        onChange={(e) => setData('education', e.target.value)}
                                    />
                                    <InputError message={errors.education} className="mt-1" />
                                </div>

                                <div>
                                    <InputLabel htmlFor="religion" value="Agama" />
                                    <TextInput
                                        id="religion"
                                        className="mt-1 block w-full"
                                        value={data.religion}
                                        onChange={(e) => setData('religion', e.target.value)}
                                    />
                                    <InputError message={errors.religion} className="mt-1" />
                                </div>

                                <div>
                                    <InputLabel
                                        htmlFor="marital_status"
                                        value="Status Perkawinan"
                                    />
                                    <TextInput
                                        id="marital_status"
                                        className="mt-1 block w-full"
                                        placeholder="Belum Kawin / Kawin / Cerai"
                                        value={data.marital_status}
                                        onChange={(e) =>
                                            setData('marital_status', e.target.value)
                                        }
                                    />
                                    <InputError
                                        message={errors.marital_status}
                                        className="mt-1"
                                    />
                                </div>
                            </div>

                            <PrimaryButton disabled={processing}>
                                Simpan Perubahan
                            </PrimaryButton>
                        </form>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
