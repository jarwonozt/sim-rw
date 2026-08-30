<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Field yang boleh diubah warga sendiri lewat halaman "Data Saya" — sengaja
 * dibatasi ke data kontak/non-identitas. NIK, nama, TTL, jenis kelamin, dan
 * hubungan keluarga tetap hanya bisa diubah Sekretaris/RW/RT lewat modul
 * Kepala Keluarga, supaya data identitas resmi tidak bisa diutak-atik warga
 * sendiri tanpa verifikasi.
 */
class UpdateOwnResidentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'occupation' => ['nullable', 'string', 'max:255'],
            'education' => ['nullable', 'string', 'max:255'],
            'religion' => ['nullable', 'string', 'max:255'],
            'marital_status' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }
}
