<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFamilyHeadRequest extends FormRequest
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
            'rt_id' => ['required', 'integer', Rule::exists('master_rt', 'id')],
            'no_kk' => ['required', 'digits:16', Rule::unique('family_heads', 'no_kk')],
            'address' => ['required', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
        ];
    }
}
