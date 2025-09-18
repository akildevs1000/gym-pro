<?php

namespace App\Http\Requests\Expense;

use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    use failedValidationWithName;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'type' => 'required|string|max:255',

            'expense_category_id' => 'required|integer|exists:expense_categories,id',
            'vendor_id' => 'required|integer|exists:vendors,id',

            'invoice_number' => 'nullable|string|max:255',

            'invoice_date' => 'nullable|date',

            'items' => 'nullable|array',
            'items.*.name' => 'required_with:items|string|max:255',  // example, adjust fields per your item structure
            'items.*.qty' => 'required_with:items|integer|min:1',
            'items.*.rate' => 'required_with:items|numeric|min:0',
            'items.*.tax' => 'required_with:items|numeric|min:0',
            'items.*.total' => 'required_with:items|numeric|min:0',

            'notes' => 'nullable|string',

            'sub_total' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'balance' => 'nullable|numeric|min:0',

            'attachments' => 'nullable|array',
            'attachments.*.file_name' => 'required_with:attachments|string|max:255',
            'attachments.*.url' => 'required_with:attachments|url',
        ];
    }
}
