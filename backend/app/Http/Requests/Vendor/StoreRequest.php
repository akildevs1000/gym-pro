<?php

namespace App\Http\Requests\Vendor;

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
            // 'expense_category_id' => 'required|exists:expense_categories,id',
            'type'              => 'required|string|max:255',
            'title'             => 'required|string|max:255',
            'first_name'        => 'required|string|max:255',
            'last_name'         => 'required|string|max:255',
            'company_name'      => 'nullable|string|max:255',
            'tax_number'        => 'nullable|string|max:255',
            'email'             => 'nullable|email|max:255',
            'work_phone'        => 'nullable|string|max:20',
            'phone_number'      => 'nullable|string|max:20',
            'country'           => 'nullable|string|max:100',
            'state'             => 'nullable|string|max:100',
            'city'              => 'nullable|string|max:100',
            'zip_code'          => 'nullable|string|max:20',
        ];
    }
}
