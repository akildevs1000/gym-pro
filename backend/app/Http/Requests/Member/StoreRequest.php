<?php

namespace App\Http\Requests\Member;

use App\Http\Controllers\Controller;
use App\Traits\failedValidation;
use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    use failedValidation;
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
            "system_user_id" => $this->system_user_id,
        ];

        $controller = new Controller;

        return [
            'title' => ['required'],
            'system_user_id' => ['required', $controller->uniqueRecord("members", $employeeDevice), 'regex:/^[1-9][0-9]*$/'],
            'first_name' => ['required', 'min:3', 'max:20'],
            'last_name' => ['required', 'min:3', 'max:20'],
            'phone_number' =>  ['required', 'min:10', 'max:13'],
            'whatsapp_number' => ['nullable', 'min:10', 'max:13'],
            'date_of_birth' => ['required'],
            'plan_start_date' => ['required'],
            'plan_end_date' => ['required'],
            'plan_id' => ['required'],
            'profile_picture' => ['image', 'mimes:jpeg,png,jpg,svg', 'max:2048', 'sometimes', 'nullable'],
            'front' => ['image', 'mimes:jpeg,png,jpg,svg', 'max:2048', 'sometimes', 'nullable'],
            'back' => ['image', 'mimes:jpeg,png,jpg,svg', 'max:2048', 'sometimes', 'nullable'],

            'payment_mode_id'  => ['required', 'exists:payment_modes,id'],
            'payment_mode_ref' => ['nullable', 'string', 'max:191'],
            'paid_amount'      => ['required', 'numeric', 'min:0'],
            'discount'      => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages()
    {
        return [
            'system_user_id.regex' => 'The Device ID should not start with zero.',
            'payment_mode_id.required' => 'The payment mode is required.',
            'paid_amount.required' => 'Please enter the paid amount.',
            'paid_amount.numeric' => 'Paid amount must be a valid number.',
        ];
    }
}
