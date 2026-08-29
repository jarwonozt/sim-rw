import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SelectInput from '@/Components/SelectInput';
import TextInput from '@/Components/TextInput';

export default function TreasuryForm({
    data,
    setData,
    errors,
    categories,
    processing,
    onSubmit,
    submitLabel,
    existingPhotoUrl,
}) {
    return (
        <form onSubmit={onSubmit} className="space-y-5" encType="multipart/form-data">
            <div>
                <InputLabel htmlFor="treasury_category_id" value="Kategori" />
                <SelectInput
                    id="treasury_category_id"
                    className="mt-1 block w-full"
                    value={data.treasury_category_id}
                    onChange={(e) => setData('treasury_category_id', e.target.value)}
                >
                    <option value="">Pilih kategori</option>
                    <optgroup label="Kas Masuk">
                        {categories
                            .filter((c) => c.type === 'in')
                            .map((c) => (
                                <option key={c.id} value={c.id}>
                                    {c.name}
                                </option>
                            ))}
                    </optgroup>
                    <optgroup label="Kas Keluar">
                        {categories
                            .filter((c) => c.type === 'out')
                            .map((c) => (
                                <option key={c.id} value={c.id}>
                                    {c.name}
                                </option>
                            ))}
                    </optgroup>
                </SelectInput>
                <InputError message={errors.treasury_category_id} className="mt-1" />
            </div>

            <div>
                <InputLabel htmlFor="amount" value="Jumlah (Rp)" />
                <TextInput
                    id="amount"
                    type="number"
                    min="1"
                    className="mt-1 block w-full"
                    value={data.amount}
                    onChange={(e) => setData('amount', e.target.value)}
                />
                <InputError message={errors.amount} className="mt-1" />
            </div>

            <div>
                <InputLabel htmlFor="transaction_date" value="Tanggal Transaksi" />
                <TextInput
                    id="transaction_date"
                    type="date"
                    className="mt-1 block w-full sm:w-56"
                    value={data.transaction_date}
                    onChange={(e) => setData('transaction_date', e.target.value)}
                />
                <InputError message={errors.transaction_date} className="mt-1" />
            </div>

            <div>
                <InputLabel htmlFor="description" value="Keterangan" />
                <TextInput
                    id="description"
                    className="mt-1 block w-full"
                    value={data.description}
                    onChange={(e) => setData('description', e.target.value)}
                />
                <InputError message={errors.description} className="mt-1" />
            </div>

            <div>
                <InputLabel htmlFor="proof_photo" value="Bukti Foto (struk/nota)" />
                {existingPhotoUrl && (
                    <img
                        src={existingPhotoUrl}
                        alt="Bukti saat ini"
                        className="mt-2 h-32 rounded-md border border-gray-200 object-cover"
                    />
                )}
                <input
                    id="proof_photo"
                    type="file"
                    accept="image/*"
                    className="mt-2 block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200"
                    onChange={(e) => setData('proof_photo', e.target.files[0])}
                />
                <InputError message={errors.proof_photo} className="mt-1" />
            </div>

            <PrimaryButton disabled={processing}>{submitLabel}</PrimaryButton>
        </form>
    );
}
