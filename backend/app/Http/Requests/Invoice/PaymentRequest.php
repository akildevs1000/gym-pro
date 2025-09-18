<?php
namespace App\Http\Requests\Invoice;

use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
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
            'invoice_id'       => 'required|exists:invoices,id',
            'payment_mode_id'  => 'required|exists:payment_modes,id',
            'payment_mode_ref' => 'nullable|string|max:255',
            'paid_amount'      => 'required|numeric|gt:0',
            'balance'      => 'required|numeric|gt:0',
        ];
    }
}
