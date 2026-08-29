<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMasterRwRequest extends FormRequest
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
            'village_id' => ['required', 'integer', Rule::exists('villages', 'id')],
            'nomor_rw' => ['required', 'string', 'max:10'],
            'ketua_rw_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'ketua_rw')],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }
}
