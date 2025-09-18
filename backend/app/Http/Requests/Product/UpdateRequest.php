<?php
namespace App\Http\Requests\Product;

use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;

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
            'name'                => 'required|unique:products,name,' . $this->route('id'),
            'description'         => ['required', 'min:3', 'max:20'],
            'product_category_id' => ['nullable'],
            'price'               => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages()
    {
        return [
            'product_category_id.required' => 'The Product Category is required.',
            'price.required'               => 'Please enter the paid amount.',
            'price.numeric'                => 'Paid amount must be a valid number.',
        ];
    }
}
