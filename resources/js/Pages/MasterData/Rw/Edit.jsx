import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SelectInput from '@/Components/SelectInput';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import { useEffect, useState } from 'react';

export default function Edit({ rw, ketuaRwOptions }) {
    const initialVillage = rw?.village;
    const initialSubdistrict = initialVillage?.subdistrict;
    const initialDistrict = initialSubdistrict?.district;
    const initialProvince = initialDistrict?.province;

    const { data, setData, put, processing, errors } = useForm({
        village_id: rw?.village_id ?? '',
        nomor_rw: rw?.nomor_rw ?? '',
        ketua_rw_id: rw?.ketua_rw_id ?? '',
        address: rw?.address ?? '',
    });

    const [provinces, setProvinces] = useState([]);
    const [districts, setDistricts] = useState(initialDistrict ? [initialDistrict] : []);
    const [subdistricts, setSubdistricts] = useState(initialSubdistrict ? [initialSubdistrict] : []);
    const [villages, setVillages] = useState(initialVillage ? [initialVillage] : []);

    const [provinceId, setProvinceId] = useState(initialProvince?.id ?? '');
    const [districtId, setDistrictId] = useState(initialDistrict?.id ?? '');
    const [subdistrictId, setSubdistrictId] = useState(initialSubdistrict?.id ?? '');

    useEffect(() => {
        axios.get(route('wilayah.provinces')).then((res) => setProvinces(res.data));
    }, []);

    useEffect(() => {
        if (!provinceId) {
            setDistricts([]);
            return;
        }
        axios
            .get(route('wilayah.districts'), { params: { province_id: provinceId } })
            .then((res) => setDistricts(res.data));
    }, [provinceId]);

    useEffect(() => {
        if (!districtId) {
            setSubdistricts([]);
            return;
        }
        axios
            .get(route('wilayah.subdistricts'), { params: { district_id: districtId } })
            .then((res) => setSubdistricts(res.data));
    }, [districtId]);

    useEffect(() => {
        if (!subdistrictId) {
            setVillages([]);
            return;
        }
        axios
            .get(route('wilayah.villages'), { params: { subdistrict_id: subdistrictId } })
            .then((res) => setVillages(res.data));
    }, [subdistrictId]);

    const submit = (e) => {
        e.preventDefault();
        put(route('rw.update'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-lg font-semibold leading-tight text-gray-900">
                    Profil RW
                </h2>
            }
        >
            <Head title="Profil RW" />

            <div className="max-w-2xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <form onSubmit={submit} className="space-y-5">
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Provinsi" />
                            <SelectInput
                                className="mt-1 w-full"
                                value={provinceId}
                                onChange={(e) => {
                                    setProvinceId(e.target.value);
                                    setDistrictId('');
                                    setSubdistrictId('');
                                    setData('village_id', '');
                                }}
                            >
                                <option value="">Pilih Provinsi</option>
                                {provinces.map((p) => (
                                    <option key={p.id} value={p.id}>
                                        {p.name}
                                    </option>
                                ))}
                            </SelectInput>
                        </div>

                        <div>
                            <InputLabel value="Kabupaten/Kota" />
                            <SelectInput
                                className="mt-1 w-full"
                                value={districtId}
                                disabled={!provinceId}
                                onChange={(e) => {
                                    setDistrictId(e.target.value);
                                    setSubdistrictId('');
                                    setData('village_id', '');
                                }}
                            >
                                <option value="">Pilih Kabupaten/Kota</option>
                                {districts.map((d) => (
                                    <option key={d.id} value={d.id}>
                                        {d.name}
                                    </option>
                                ))}
                            </SelectInput>
                        </div>

                        <div>
                            <InputLabel value="Kecamatan" />
                            <SelectInput
                                className="mt-1 w-full"
                                value={subdistrictId}
                                disabled={!districtId}
                                onChange={(e) => {
                                    setSubdistrictId(e.target.value);
                                    setData('village_id', '');
                                }}
                            >
                                <option value="">Pilih Kecamatan</option>
                                {subdistricts.map((s) => (
                                    <option key={s.id} value={s.id}>
                                        {s.name}
                                    </option>
                                ))}
                            </SelectInput>
                        </div>

                        <div>
                            <InputLabel value="Kelurahan/Desa" />
                            <SelectInput
                                className="mt-1 w-full"
                                value={data.village_id}
                                disabled={!subdistrictId}
                                onChange={(e) => setData('village_id', e.target.value)}
                            >
                                <option value="">Pilih Kelurahan/Desa</option>
                                {villages.map((v) => (
                                    <option key={v.id} value={v.id}>
                                        {v.name}
                                    </option>
                                ))}
                            </SelectInput>
                            <InputError message={errors.village_id} className="mt-1" />
                        </div>
                    </div>

                    <div>
                        <InputLabel htmlFor="nomor_rw" value="Nomor RW" />
                        <TextInput
                            id="nomor_rw"
                            className="mt-1 block w-full sm:w-40"
                            value={data.nomor_rw}
                            onChange={(e) => setData('nomor_rw', e.target.value)}
                        />
                        <InputError message={errors.nomor_rw} className="mt-1" />
                    </div>

                    <div>
                        <InputLabel htmlFor="ketua_rw_id" value="Ketua RW" />
                        <SelectInput
                            id="ketua_rw_id"
                            className="mt-1 block w-full sm:w-80"
                            value={data.ketua_rw_id}
                            onChange={(e) => setData('ketua_rw_id', e.target.value)}
                        >
                            <option value="">— Belum ditentukan —</option>
                            {ketuaRwOptions.map((u) => (
                                <option key={u.id} value={u.id}>
                                    {u.name}
                                </option>
                            ))}
                        </SelectInput>
                        <InputError message={errors.ketua_rw_id} className="mt-1" />
                    </div>

                    <div>
                        <InputLabel htmlFor="address" value="Alamat Sekretariat" />
                        <TextInput
                            id="address"
                            className="mt-1 block w-full"
                            value={data.address}
                            onChange={(e) => setData('address', e.target.value)}
                        />
                        <InputError message={errors.address} className="mt-1" />
                    </div>

                    <PrimaryButton disabled={processing}>Simpan</PrimaryButton>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
