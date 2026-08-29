import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SelectInput from '@/Components/SelectInput';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    Combobox,
    ComboboxInput,
    ComboboxOption,
    ComboboxOptions,
} from '@headlessui/react';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import { useEffect, useState } from 'react';

export default function Create({ templates, preselectedResident }) {
    const { data, setData, post, processing, errors } = useForm({
        resident_id: preselectedResident?.id ?? '',
        letter_template_id: '',
        purpose: '',
    });

    const [query, setQuery] = useState('');
    const [options, setOptions] = useState(
        preselectedResident ? [preselectedResident] : [],
    );
    const [selectedResident, setSelectedResident] = useState(
        preselectedResident ?? null,
    );

    useEffect(() => {
        if (query.length < 2) {
            setOptions([]);
            return;
        }

        const timeout = setTimeout(() => {
            axios
                .get(route('residents.search'), { params: { q: query } })
                .then((res) => setOptions(res.data));
        }, 300);

        return () => clearTimeout(timeout);
    }, [query]);

    const submit = (e) => {
        e.preventDefault();
        post(route('letters.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Buat Surat
                </h2>
            }
        >
            <Head title="Buat Surat" />

            <div className="max-w-xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <form onSubmit={submit} className="space-y-5">
                    <div>
                        <InputLabel value="Cari Penduduk (Nama/NIK)" />
                        <Combobox
                            value={selectedResident}
                            onChange={(resident) => {
                                setSelectedResident(resident);
                                setData('resident_id', resident?.id ?? '');
                            }}
                        >
                            <div className="relative mt-1">
                                <ComboboxInput
                                    className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    displayValue={(resident) => resident?.name ?? ''}
                                    placeholder="Ketik minimal 2 huruf..."
                                    onChange={(e) => setQuery(e.target.value)}
                                />
                                {options.length > 0 && (
                                    <ComboboxOptions className="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-sm shadow-lg ring-1 ring-black/5">
                                        {options.map((resident) => (
                                            <ComboboxOption
                                                key={resident.id}
                                                value={resident}
                                                className="cursor-pointer px-3 py-2 data-[focus]:bg-emerald-50"
                                            >
                                                <div className="font-medium text-gray-900">
                                                    {resident.name}
                                                </div>
                                                <div className="text-xs text-gray-500">
                                                    NIK {resident.nik} · {resident.address}
                                                </div>
                                            </ComboboxOption>
                                        ))}
                                    </ComboboxOptions>
                                )}
                            </div>
                        </Combobox>
                        <InputError message={errors.resident_id} className="mt-1" />
                    </div>

                    <div>
                        <InputLabel htmlFor="letter_template_id" value="Jenis Surat" />
                        <SelectInput
                            id="letter_template_id"
                            className="mt-1 block w-full"
                            value={data.letter_template_id}
                            onChange={(e) => setData('letter_template_id', e.target.value)}
                        >
                            <option value="">Pilih jenis surat</option>
                            {templates.map((template) => (
                                <option key={template.id} value={template.id}>
                                    {template.name}
                                </option>
                            ))}
                        </SelectInput>
                        <InputError message={errors.letter_template_id} className="mt-1" />
                    </div>

                    <div>
                        <InputLabel htmlFor="purpose" value="Tujuan / Keperluan" />
                        <TextInput
                            id="purpose"
                            className="mt-1 block w-full"
                            placeholder="Contoh: Melengkapi berkas pengajuan KUR"
                            value={data.purpose}
                            onChange={(e) => setData('purpose', e.target.value)}
                        />
                        <InputError message={errors.purpose} className="mt-1" />
                    </div>

                    <PrimaryButton disabled={processing}>Terbitkan Surat</PrimaryButton>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
