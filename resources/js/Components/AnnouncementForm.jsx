import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextareaInput from '@/Components/TextareaInput';
import TextInput from '@/Components/TextInput';

export default function AnnouncementForm({
    data,
    setData,
    errors,
    processing,
    onSubmit,
    submitLabel,
    existingImageUrl,
}) {
    return (
        <form onSubmit={onSubmit} className="space-y-5">
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
                <InputLabel htmlFor="content" value="Isi Pengumuman" />
                <TextareaInput
                    id="content"
                    rows={6}
                    className="mt-1 block w-full"
                    value={data.content}
                    onChange={(e) => setData('content', e.target.value)}
                />
                <InputError message={errors.content} className="mt-1" />
            </div>

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel htmlFor="publish_date" value="Tanggal Tayang" />
                    <TextInput
                        id="publish_date"
                        type="date"
                        className="mt-1 block w-full"
                        value={data.publish_date}
                        onChange={(e) => setData('publish_date', e.target.value)}
                    />
                    <InputError message={errors.publish_date} className="mt-1" />
                </div>

                <div>
                    <InputLabel htmlFor="expire_date" value="Tanggal Kadaluarsa (opsional)" />
                    <TextInput
                        id="expire_date"
                        type="date"
                        className="mt-1 block w-full"
                        value={data.expire_date ?? ''}
                        onChange={(e) => setData('expire_date', e.target.value)}
                    />
                    <InputError message={errors.expire_date} className="mt-1" />
                </div>
            </div>

            <div>
                <InputLabel htmlFor="image" value="Gambar (opsional)" />
                {existingImageUrl && (
                    <img
                        src={existingImageUrl}
                        alt="Gambar saat ini"
                        className="mt-2 h-32 rounded-md border border-gray-200 object-cover"
                    />
                )}
                <input
                    id="image"
                    type="file"
                    accept="image/*"
                    className="mt-2 block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200"
                    onChange={(e) => setData('image', e.target.files[0])}
                />
                <InputError message={errors.image} className="mt-1" />
            </div>

            <PrimaryButton disabled={processing}>{submitLabel}</PrimaryButton>
        </form>
    );
}
