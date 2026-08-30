<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryLoanRequest extends FormRequest
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
            'inventory_item_id' => ['required', 'integer', Rule::exists('inventory_items', 'id')],
            'resident_id' => ['nullable', 'integer', Rule::exists('residents', 'id')],
            'borrower_name' => ['required', 'string', 'max:255'],
            'borrower_phone' => ['nullable', 'string', 'max:20'],
            'quantity_borrowed' => ['required', 'integer', 'min:1'],
            'purpose' => ['required', 'string', 'max:255'],
            'loan_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:loan_date'],
        ];
    }
}
