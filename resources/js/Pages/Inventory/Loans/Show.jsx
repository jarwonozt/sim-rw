import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SelectInput from '@/Components/SelectInput';
import TextareaInput from '@/Components/TextareaInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { LOAN_STATUS_COLORS, LOAN_STATUS_LABELS } from '@/Utils/inventoryStatus';
import { Head, useForm } from '@inertiajs/react';

function formatDate(value) {
    return new Date(value).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

export default function Show({ loan }) {
    const { data, setData, patch, processing, errors } = useForm({
        returned_condition: 'baik',
        notes: '',
    });

    const submit = (e) => {
        e.preventDefault();
        patch(route('inventory-loans.return', loan.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Detail Peminjaman
                </h2>
            }
        >
            <Head title="Detail Peminjaman" />

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm lg:col-span-2">
                    <div className="flex items-start justify-between gap-4">
                        <div>
                            <h3 className="text-lg font-semibold text-gray-900">
                                {loan.item?.name}
                            </h3>
                            <p className="mt-1 text-sm text-gray-500">
                                Dipinjam oleh {loan.borrower_name}
                                {loan.borrower_phone ? ` · ${loan.borrower_phone}` : ''}
                            </p>
                        </div>
                        <span
                            className={
                                'shrink-0 rounded px-2 py-0.5 text-xs font-medium ' +
                                (loan.is_overdue
                                    ? 'bg-rose-50 text-rose-700'
                                    : LOAN_STATUS_COLORS[loan.status])
                            }
                        >
                            {loan.is_overdue ? 'Terlambat' : LOAN_STATUS_LABELS[loan.status]}
                        </span>
                    </div>

                    <dl className="mt-4 grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt className="text-gray-500">Jumlah</dt>
                            <dd className="text-gray-900">{loan.quantity_borrowed}</dd>
                        </div>
                        <div>
                            <dt className="text-gray-500">Keperluan</dt>
                            <dd className="text-gray-900">{loan.purpose}</dd>
                        </div>
                        <div>
                            <dt className="text-gray-500">Tanggal Pinjam</dt>
                            <dd className="text-gray-900">{formatDate(loan.loan_date)}</dd>
                        </div>
                        <div>
                            <dt className="text-gray-500">Rencana Kembali</dt>
                            <dd className="text-gray-900">{formatDate(loan.due_date)}</dd>
                        </div>
                        {loan.return_date && (
                            <div>
                                <dt className="text-gray-500">Tanggal Kembali</dt>
                                <dd className="text-gray-900">{formatDate(loan.return_date)}</dd>
                            </div>
                        )}
                        <div>
                            <dt className="text-gray-500">Dicatat Oleh</dt>
                            <dd className="text-gray-900">{loan.handled_by?.name}</dd>
                        </div>
                    </dl>

                    {loan.notes && (
                        <p className="mt-4 whitespace-pre-line text-sm text-gray-700">
                            {loan.notes}
                        </p>
                    )}

                    {loan.status === 'dipinjam' && (
                        <form onSubmit={submit} className="mt-6 border-t border-gray-100 pt-4">
                            <InputLabel htmlFor="returned_condition" value="Tandai Barang Kembali" />
                            <SelectInput
                                id="returned_condition"
                                className="mt-1 block w-full sm:w-64"
                                value={data.returned_condition}
                                onChange={(e) => setData('returned_condition', e.target.value)}
                            >
                                <option value="baik">Baik</option>
                                <option value="rusak_ringan">Rusak Ringan</option>
                                <option value="rusak_berat">Rusak Berat</option>
                                <option value="hilang">Hilang</option>
                            </SelectInput>
                            <InputError message={errors.returned_condition} className="mt-1" />

                            <div className="mt-3">
                                <InputLabel htmlFor="notes" value="Catatan (opsional)" />
                                <TextareaInput
                                    id="notes"
                                    rows={2}
                                    className="mt-1 block w-full"
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                />
                                <InputError message={errors.notes} className="mt-1" />
                            </div>

                            <PrimaryButton className="mt-3" disabled={processing}>
                                Catat Pengembalian
                            </PrimaryButton>
                        </form>
                    )}
                </div>

                <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 className="text-sm font-semibold text-gray-900">Info Barang</h3>
                    <dl className="mt-4 space-y-3 text-sm">
                        <div>
                            <dt className="text-gray-500">Kode</dt>
                            <dd className="font-mono text-gray-900">{loan.item?.code}</dd>
                        </div>
                        <div>
                            <dt className="text-gray-500">Kategori</dt>
                            <dd className="text-gray-900">{loan.item?.category?.name}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
