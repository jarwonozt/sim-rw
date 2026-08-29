import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextareaInput from '@/Components/TextareaInput';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        description: '',
        photo: null,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('complaints.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Ajukan Pengaduan
                </h2>
            }
        >
            <Head title="Ajukan Pengaduan" />

            <div className="max-w-xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <form onSubmit={submit} className="space-y-5">
                    <div>
                        <InputLabel htmlFor="title" value="Judul" />
                        <TextInput
                            id="title"
                            className="mt-1 block w-full"
                            value={data.title}
                            onChange={(e) => setData('title', e.target.value)}
                            isFocused
                        />
                        <InputError message={errors.title} className="mt-1" />
                    </div>

                    <div>
                        <InputLabel htmlFor="description" value="Deskripsi" />
                        <TextareaInput
                            id="description"
                            rows={5}
                            className="mt-1 block w-full"
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                        />
                        <InputError message={errors.description} className="mt-1" />
                    </div>

                    <div>
                        <InputLabel htmlFor="photo" value="Foto Pendukung (opsional)" />
                        <input
                            id="photo"
                            type="file"
                            accept="image/*"
                            className="mt-2 block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200"
                            onChange={(e) => setData('photo', e.target.files[0])}
                        />
                        <InputError message={errors.photo} className="mt-1" />
                    </div>

                    <PrimaryButton disabled={processing}>Kirim Pengaduan</PrimaryButton>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
