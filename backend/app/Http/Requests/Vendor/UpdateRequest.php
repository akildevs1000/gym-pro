<?php

namespace App\Http\Requests\Vendor;

use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
            'type'              => 'sometimes|string|max:255',
            'title'             => 'sometimes|string|max:255',
            'first_name'        => 'sometimes|string|max:255',
            'last_name'         => 'sometimes|string|max:255',
            'company_name'      => 'nullable|string|max:255',
            'tax_number'        => 'nullable|string|max:255',
            'email'             => 'nullable|email|max:255',
            'work_phone'        => 'nullable|string|max:20',
            'phone_number'      => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('vendors', 'phone_number')->ignore($this->vendor->id),
            ],
            'country'           => 'nullable|string|max:100',
            'state'             => 'nullable|string|max:100',
            'city'              => 'nullable|string|max:100',
            'zip_code'          => 'nullable|string|max:20',
        ];
    }
}
