<?php
namespace App\Http\Requests\Quotation;

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
            'member_id'        => 'required|exists:members,id',
            'items'            => 'required|array',
            'items.*.name'     => 'required|string',
            'items.*.qty'      => 'required|numeric',
            'items.*.rate'     => 'required|numeric',
            'items.*.tax'      => 'required|numeric',
            'items.*.discount' => 'required|numeric',
            'items.*.total'    => 'required|numeric',
            'sub_total'        => 'required|numeric',
            'tax'              => 'required|numeric',
            'discount'         => 'required|numeric',
            'total'            => 'required|numeric',
            'notes'            => 'nullable|string',
        ];
    }

    // public function messages()
    // {
    //     return [
    //         'product_category_id.required' => 'The Product Category is required.',
    //         'price.required'               => 'Please enter the paid amount.',
    //         'price.numeric'                => 'Paid amount must be a valid number.',
    //     ];
    // }
}
