<?php
namespace App\Http\Requests\Member;

use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;

class MembershipUpdateRequest extends FormRequest
{
    use failedValidationWithName;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'total'   => 'required|numeric|min:0',
            'plan_id' => 'required',
        ];
    }
}
