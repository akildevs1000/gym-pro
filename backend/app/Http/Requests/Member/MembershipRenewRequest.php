<?php
namespace App\Http\Requests\Member;

use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;

class MembershipRenewRequest extends FormRequest
{
    use failedValidationWithName;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_start_date'  => 'required|date',
            'plan_end_date'    => 'required|date|after:plan_start_date',
            'payment_mode_id'  => 'required|exists:payment_modes,id',
            'payment_mode_ref' => 'nullable|string|max:255',
            'plan_price'       => 'required|numeric|min:0',
            'discount'         => 'nullable|numeric|min:0',
            'balance'          => 'nullable|numeric|min:0',
            'paid_amount'      => 'required|numeric|min:0',

            'member_id'        => 'required',
            'plan_id'          => 'required',

        ];
    }
}
