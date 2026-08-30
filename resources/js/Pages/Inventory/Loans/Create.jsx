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

export default function Create({ items }) {
    const today = new Date().toISOString().slice(0, 10);

    const { data, setData, post, processing, errors } = useForm({
        inventory_item_id: '',
        resident_id: '',
        borrower_name: '',
        borrower_phone: '',
        quantity_borrowed: 1,
        purpose: '',
        loan_date: today,
        due_date: today,
    });

    const [query, setQuery] = useState('');
    const [options, setOptions] = useState([]);
    const [selectedResident, setSelectedResident] = useState(null);

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

    const selectedItem = items.find((item) => item.id === Number(data.inventory_item_id));

    const submit = (e) => {
        e.preventDefault();
        post(route('inventory-loans.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Catat Peminjaman
                </h2>
            }
        >
            <Head title="Catat Peminjaman" />

            <div className="max-w-xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <form onSubmit={submit} className="space-y-5">
                    <div>
                        <InputLabel htmlFor="inventory_item_id" value="Barang" />
                        <SelectInput
                            id="inventory_item_id"
                            className="mt-1 block w-full"
                            value={data.inventory_item_id}
                            onChange={(e) => setData('inventory_item_id', e.target.value)}
                        >
                            <option value="">Pilih Barang</option>
                            {items.map((item) => (
                                <option key={item.id} value={item.id}>
                                    {item.name} ({item.code}) — tersedia {item.available_quantity}
                                </option>
                            ))}
                        </SelectInput>
                        <InputError message={errors.inventory_item_id} className="mt-1" />
                        {items.length === 0 && (
                            <p className="mt-1 text-sm text-amber-600">
                                Tidak ada barang dengan stok tersedia saat ini.
                            </p>
                        )}
                    </div>

                    <div>
                        <InputLabel htmlFor="quantity_borrowed" value="Jumlah Dipinjam" />
                        <TextInput
                            id="quantity_borrowed"
                            type="number"
                            min="1"
                            max={selectedItem?.available_quantity ?? undefined}
                            className="mt-1 block w-full sm:w-40"
                            value={data.quantity_borrowed}
                            onChange={(e) => setData('quantity_borrowed', e.target.value)}
                        />
                        <InputError message={errors.quantity_borrowed} className="mt-1" />
                    </div>

                    <div>
                        <InputLabel value="Cari Warga Terdaftar (opsional)" />
                        <Combobox
                            value={selectedResident}
                            onChange={(resident) => {
                                setSelectedResident(resident);
                                setData('resident_id', resident?.id ?? '');
                                if (resident?.name) {
                                    setData('borrower_name', resident.name);
                                }
                            }}
                        >
                            <div className="relative mt-1">
                                <ComboboxInput
                                    className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    displayValue={(resident) => resident?.name ?? ''}
                                    placeholder="Ketik minimal 2 huruf, atau lewati jika bukan warga terdaftar..."
                                    onChange={(e) => setQuery(e.target.value)}
                                />
                                {options.length > 0 && (
                                    <ComboboxOptions className="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5">
                                        {options.map((resident) => (
                                            <ComboboxOption
                                                key={resident.id}
                                                value={resident}
                                                className="cursor-pointer px-3 py-2 text-sm data-[focus]:bg-emerald-50"
                                            >
                                                {resident.name} — {resident.nik}
                                            </ComboboxOption>
                                        ))}
                                    </ComboboxOptions>
                                )}
                            </div>
                        </Combobox>
                    </div>

                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel htmlFor="borrower_name" value="Nama Peminjam" />
                            <TextInput
                                id="borrower_name"
                                className="mt-1 block w-full"
                                value={data.borrower_name}
                                onChange={(e) => setData('borrower_name', e.target.value)}
                            />
                            <InputError message={errors.borrower_name} className="mt-1" />
                        </div>

                        <div>
                            <InputLabel htmlFor="borrower_phone" value="No. HP (opsional)" />
                            <TextInput
                                id="borrower_phone"
                                className="mt-1 block w-full"
                                value={data.borrower_phone}
                                onChange={(e) => setData('borrower_phone', e.target.value)}
                            />
                            <InputError message={errors.borrower_phone} className="mt-1" />
                        </div>
                    </div>

                    <div>
                        <InputLabel htmlFor="purpose" value="Keperluan" />
                        <TextInput
                            id="purpose"
                            className="mt-1 block w-full"
                            value={data.purpose}
                            onChange={(e) => setData('purpose', e.target.value)}
                        />
                        <InputError message={errors.purpose} className="mt-1" />
                    </div>

                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel htmlFor="loan_date" value="Tanggal Pinjam" />
                            <TextInput
                                id="loan_date"
                                type="date"
                                className="mt-1 block w-full"
                                value={data.loan_date}
                                onChange={(e) => setData('loan_date', e.target.value)}
                            />
                            <InputError message={errors.loan_date} className="mt-1" />
                        </div>

                        <div>
                            <InputLabel htmlFor="due_date" value="Rencana Kembali" />
                            <TextInput
                                id="due_date"
                                type="date"
                                className="mt-1 block w-full"
                                value={data.due_date}
                                onChange={(e) => setData('due_date', e.target.value)}
                            />
                            <InputError message={errors.due_date} className="mt-1" />
                        </div>
                    </div>

                    <PrimaryButton disabled={processing}>Simpan</PrimaryButton>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
