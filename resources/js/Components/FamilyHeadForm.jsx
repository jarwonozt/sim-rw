import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SelectInput from '@/Components/SelectInput';
import TextInput from '@/Components/TextInput';

export default function FamilyHeadForm({ data, setData, errors, rtOptions, processing, onSubmit, submitLabel }) {
    return (
        <form onSubmit={onSubmit} className="space-y-5">
            <div>
                <InputLabel htmlFor="no_kk" value="Nomor KK (16 digit)" />
                <TextInput
                    id="no_kk"
                    className="mt-1 block w-full sm:w-72"
                    maxLength={16}
                    value={data.no_kk}
                    onChange={(e) => setData('no_kk', e.target.value)}
                    isFocused
                />
                <InputError message={errors.no_kk} className="mt-1" />
            </div>

            <div>
                <InputLabel htmlFor="rt_id" value="RT" />
                <SelectInput
                    id="rt_id"
                    className="mt-1 block w-full sm:w-40"
                    value={data.rt_id}
                    onChange={(e) => setData('rt_id', e.target.value)}
                >
                    <option value="">Pilih RT</option>
                    {rtOptions.map((rt) => (
                        <option key={rt.id} value={rt.id}>
                            RT {rt.nomor_rt}
                        </option>
                    ))}
                </SelectInput>
                <InputError message={errors.rt_id} className="mt-1" />
            </div>

            <div>
                <InputLabel htmlFor="address" value="Alamat" />
                <TextInput
                    id="address"
                    className="mt-1 block w-full"
                    value={data.address}
                    onChange={(e) => setData('address', e.target.value)}
                />
                <InputError message={errors.address} className="mt-1" />
            </div>

            <div>
                <InputLabel htmlFor="postal_code" value="Kode Pos" />
                <TextInput
                    id="postal_code"
                    className="mt-1 block w-full sm:w-40"
                    value={data.postal_code}
                    onChange={(e) => setData('postal_code', e.target.value)}
                />
                <InputError message={errors.postal_code} className="mt-1" />
            </div>

            <PrimaryButton disabled={processing}>{submitLabel}</PrimaryButton>
        </form>
    );
}
