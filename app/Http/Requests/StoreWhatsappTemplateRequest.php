<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWhatsappTemplateRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'event_key' => [
                'nullable',
                'string',
                Rule::in(['complaint_resolved']),
                Rule::unique('whatsapp_templates', 'event_key')->ignore($this->route('whatsappTemplate')),
            ],
            'content' => ['required', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }
}
