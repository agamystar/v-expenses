<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Both admin and staff can create expenses
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:expense_categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check if category is active
            if ($this->category_id) {
                $category = \App\Models\ExpenseCategory::find($this->category_id);
                if ($category && !$category->is_active) {
                    $validator->errors()->add('category_id', 'The selected category is not active.');
                }
            }

            // Check if vendor is active (if provided)
            if ($this->vendor_id) {
                $vendor = \App\Models\Vendor::find($this->vendor_id);
                if ($vendor && !$vendor->is_active) {
                    $validator->errors()->add('vendor_id', 'The selected vendor is not active.');
                }
            }
        });
    }
}
