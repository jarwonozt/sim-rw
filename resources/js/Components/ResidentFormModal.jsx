import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import SelectInput from '@/Components/SelectInput';
import TextInput from '@/Components/TextInput';
import { useForm } from '@inertiajs/react';

const EMPTY_FORM = {
    nik: '',
    name: '',
    gender: 'L',
    birth_place: '',
    birth_date: '',
    is_family_head: false,
    relationship_status: '',
    occupation: '',
    religion: '',
    education: '',
    marital_status: '',
    phone: '',
};

export default function ResidentFormModal({ show, onClose, resident, familyHeadId }) {
    const isEditing = Boolean(resident);

    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm(
        resident
            ? {
                  nik: resident.nik,
                  name: resident.name,
                  gender: resident.gender,
                  birth_place: resident.birth_place ?? '',
                  birth_date: resident.birth_date ? resident.birth_date.slice(0, 10) : '',
                  is_family_head: resident.is_family_head,
                  relationship_status: resident.relationship_status ?? '',
                  occupation: resident.occupation ?? '',
                  religion: resident.religion ?? '',
                  education: resident.education ?? '',
                  marital_status: resident.marital_status ?? '',
                  phone: resident.phone ?? '',
              }
            : EMPTY_FORM,
    );

    const close = () => {
        reset();
        clearErrors();
        onClose();
    };

    const submit = (e) => {
        e.preventDefault();

        const options = { onSuccess: () => close() };

        if (isEditing) {
            put(route('residents.update', resident.id), options);
        } else {
            post(route('residents.store', familyHeadId), options);
        }
    };

    return (
        <Modal show={show} onClose={close} maxWidth="lg">
            <form onSubmit={submit} className="max-h-[80vh] overflow-y-auto p-6">
                <h2 className="text-lg font-semibold text-gray-900">
                    {isEditing ? 'Edit Penduduk' : 'Tambah Penduduk'}
                </h2>

                <div className="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel htmlFor="nik" value="NIK (16 digit)" />
                        <TextInput
                            id="nik"
                            className="mt-1 block w-full"
                            maxLength={16}
                            value={data.nik}
                            onChange={(e) => setData('nik', e.target.value)}
                            isFocused
                        />
                        <InputError message={errors.nik} className="mt-1" />
                    </div>

                    <div>
                        <InputLabel htmlFor="name" value="Nama Lengkap" />
                        <TextInput
                            id="name"
                            className="mt-1 block w-full"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                        />
                        <InputError message={errors.name} className="mt-1" />
                    </div>

                    <div>
                        <InputLabel htmlFor="gender" value="Jenis Kelamin" />
                        <SelectInput
                            id="gender"
                            className="mt-1 block w-full"
                            value={data.gender}
                            onChange={(e) => setData('gender', e.target.value)}
                        >
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </SelectInput>
                        <InputError message={errors.gender} className="mt-1" />
                    </div>

                    <div>
                        <InputLabel htmlFor="relationship_status" value="Hubungan dalam Keluarga" />
                        <TextInput
                            id="relationship_status"
                            className="mt-1 block w-full"
                            placeholder="Kepala Keluarga / Istri / Anak"
                            value={data.relationship_status}
                            onChange={(e) => setData('relationship_status', e.target.value)}
                        />
                        <InputError message={errors.relationship_status} className="mt-1" />
                    </div>

                    <div>
                        <InputLabel htmlFor="birth_place" value="Tempat Lahir" />
                        <TextInput
                            id="birth_place"
                            className="mt-1 block w-full"
                            value={data.birth_place}
                            onChange={(e) => setData('birth_place', e.target.value)}
                        />
                        <InputError message={errors.birth_place} className="mt-1" />
                    </div>

                    <div>
                        <InputLabel htmlFor="birth_date" value="Tanggal Lahir" />
                        <TextInput
                            id="birth_date"
                            type="date"
                            className="mt-1 block w-full"
                            value={data.birth_date}
                            onChange={(e) => setData('birth_date', e.target.value)}
                        />
                        <InputError message={errors.birth_date} className="mt-1" />
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
                        <InputLabel htmlFor="phone" value="No. HP" />
                        <TextInput
                            id="phone"
                            className="mt-1 block w-full"
                            value={data.phone}
                            onChange={(e) => setData('phone', e.target.value)}
                        />
                        <InputError message={errors.phone} className="mt-1" />
                    </div>
                </div>

                <label className="mt-4 flex items-center gap-2">
                    <Checkbox
                        checked={data.is_family_head}
                        onChange={(e) => setData('is_family_head', e.target.checked)}
                    />
                    <span className="text-sm text-gray-700">
                        Jadikan Kepala Keluarga
                    </span>
                </label>

                <div className="mt-6 flex justify-end gap-3">
                    <SecondaryButton onClick={close}>Batal</SecondaryButton>
                    <PrimaryButton disabled={processing}>Simpan</PrimaryButton>
                </div>
            </form>
        </Modal>
    );
}
