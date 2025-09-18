<?php

namespace App\Http\Requests\Member;

use App\Http\Controllers\Controller;
use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    use failedValidationWithName;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeDevice = [
            "system_user_id" => $this->system_user_id,
        ];

        // Get current member's ID from route (e.g., route('member'))
        $memberId = $this->route('member')?->id ?? null;

        $controller = new Controller;

        return [
            'title' => ['required'],
            'system_user_id' => [
                'required',
                $controller->uniqueRecord("employees", $employeeDevice, $memberId),
                'regex:/^[1-9][0-9]*$/'
            ],
            'first_name' => ['required', 'min:3', 'max:20'],
            'last_name' => ['required', 'min:3', 'max:20'],
            'phone_number' =>  ['required', 'min:10', 'max:13'],
            'whatsapp_number' => ['nullable', 'min:10', 'max:13'],
            'profile_picture' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,svg', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'system_user_id.regex' => 'The Device ID should not start with zero.',
        ];
    }
}
