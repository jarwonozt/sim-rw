import InputError from '@/Components/InputError';
import Modal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import { useForm } from '@inertiajs/react';

export default function ResidentImportModal({ show, onClose }) {
    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        file: null,
    });

    const close = () => {
        reset();
        clearErrors();
        onClose();
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('residents.import'), { onSuccess: () => close() });
    };

    return (
        <Modal show={show} onClose={close} maxWidth="md">
            <form onSubmit={submit} className="p-6">
                <h2 className="text-lg font-semibold text-gray-900">Import Data Penduduk</h2>
                <p className="mt-2 text-sm text-gray-600">
                    Unggah berkas .xlsx sesuai format template. Data KK dan Penduduk yang
                    sudah ada (berdasarkan No. KK/NIK) akan diperbarui, yang baru akan
                    ditambahkan.
                </p>

                <a
                    href={route('residents.import-template')}
                    className="mt-3 inline-block text-sm font-medium text-emerald-700 hover:underline"
                >
                    Unduh template
                </a>

                <div className="mt-4">
                    <input
                        type="file"
                        accept=".xlsx,.xls,.csv"
                        className="block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200"
                        onChange={(e) => setData('file', e.target.files[0])}
                    />
                    <InputError message={errors.file} className="mt-1" />
                </div>

                <div className="mt-6 flex justify-end gap-3">
                    <SecondaryButton onClick={close}>Batal</SecondaryButton>
                    <PrimaryButton disabled={processing || !data.file}>
                        {processing ? 'Mengimpor...' : 'Import'}
                    </PrimaryButton>
                </div>
            </form>
        </Modal>
    );
}
