import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SelectInput from '@/Components/SelectInput';
import TextareaInput from '@/Components/TextareaInput';
import TextInput from '@/Components/TextInput';

export default function InventoryItemForm({
    data,
    setData,
    errors,
    categoryOptions,
    rtOptions,
    isKetuaRt,
    processing,
    onSubmit,
    submitLabel,
    existingPhotoUrl,
}) {
    return (
        <form onSubmit={onSubmit} className="space-y-5">
            <div>
                <InputLabel htmlFor="name" value="Nama Barang" />
                <TextInput
                    id="name"
                    className="mt-1 block w-full"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    isFocused
                />
                <InputError message={errors.name} className="mt-1" />
            </div>

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel htmlFor="inventory_category_id" value="Kategori" />
                    <SelectInput
                        id="inventory_category_id"
                        className="mt-1 block w-full"
                        value={data.inventory_category_id}
                        onChange={(e) => setData('inventory_category_id', e.target.value)}
                    >
                        <option value="">Pilih Kategori</option>
                        {categoryOptions.map((category) => (
                            <option key={category.id} value={category.id}>
                                {category.name}
                            </option>
                        ))}
                    </SelectInput>
                    <InputError message={errors.inventory_category_id} className="mt-1" />
                </div>

                <div>
                    <InputLabel htmlFor="rt_id" value="Kepemilikan" />
                    {isKetuaRt ? (
                        <p className="mt-1 flex h-[42px] items-center rounded-md border border-gray-200 bg-gray-50 px-3 text-sm text-gray-600">
                            RT {rtOptions[0]?.nomor_rt} (RT Anda)
                        </p>
                    ) : (
                        <SelectInput
                            id="rt_id"
                            className="mt-1 block w-full"
                            value={data.rt_id ?? ''}
                            onChange={(e) => setData('rt_id', e.target.value)}
                        >
                            <option value="">RW Pusat (semua RT)</option>
                            {rtOptions.map((rt) => (
                                <option key={rt.id} value={rt.id}>
                                    RT {rt.nomor_rt}
                                </option>
                            ))}
                        </SelectInput>
                    )}
                    <InputError message={errors.rt_id} className="mt-1" />
                </div>
            </div>

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel htmlFor="quantity" value="Jumlah" />
                    <TextInput
                        id="quantity"
                        type="number"
                        min="1"
                        className="mt-1 block w-full"
                        value={data.quantity}
                        onChange={(e) => setData('quantity', e.target.value)}
                    />
                    <InputError message={errors.quantity} className="mt-1" />
                </div>

                <div>
                    <InputLabel htmlFor="condition" value="Kondisi" />
                    <SelectInput
                        id="condition"
                        className="mt-1 block w-full"
                        value={data.condition}
                        onChange={(e) => setData('condition', e.target.value)}
                    >
                        <option value="baik">Baik</option>
                        <option value="rusak_ringan">Rusak Ringan</option>
                        <option value="rusak_berat">Rusak Berat</option>
                        <option value="hilang">Hilang</option>
                    </SelectInput>
                    <InputError message={errors.condition} className="mt-1" />
                </div>
            </div>

            <div>
                <InputLabel htmlFor="location" value="Lokasi Penyimpanan (opsional)" />
                <TextInput
                    id="location"
                    className="mt-1 block w-full"
                    value={data.location ?? ''}
                    onChange={(e) => setData('location', e.target.value)}
                />
                <InputError message={errors.location} className="mt-1" />
            </div>

            <div>
                <InputLabel htmlFor="notes" value="Catatan (opsional)" />
                <TextareaInput
                    id="notes"
                    rows={3}
                    className="mt-1 block w-full"
                    value={data.notes ?? ''}
                    onChange={(e) => setData('notes', e.target.value)}
                />
                <InputError message={errors.notes} className="mt-1" />
            </div>

            <div>
                <InputLabel htmlFor="photo" value="Foto (opsional)" />
                {existingPhotoUrl && (
                    <img
                        src={existingPhotoUrl}
                        alt="Foto saat ini"
                        className="mt-2 h-32 rounded-md border border-gray-200 object-cover"
                    />
                )}
                <input
                    id="photo"
                    type="file"
                    accept="image/*"
                    className="mt-2 block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200"
                    onChange={(e) => setData('photo', e.target.files[0])}
                />
                <InputError message={errors.photo} className="mt-1" />
            </div>

            <PrimaryButton disabled={processing}>{submitLabel}</PrimaryButton>
        </form>
    );
}
