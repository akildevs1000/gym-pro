<?php
namespace App\Http\Requests\Product;

use App\Http\Controllers\Controller;
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

        $employeeDevice = [
            "name" => $this->name,
        ];

        $controller = new Controller;

        return [
            'name'                => ['required', $controller->uniqueRecord("products", $employeeDevice), 'max:255'],
            'description'         => ['required', 'min:3', 'max:20'],
            'product_category_id' => ['nullable'],
            'price'               => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages()
    {
        return [
            'product_category_id.required' => 'The Product Category is required.',
            'price.required'     => 'Please enter the paid amount.',
            'price.numeric'      => 'Paid amount must be a valid number.',
        ];
    }
}
