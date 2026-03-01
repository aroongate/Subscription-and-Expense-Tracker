<?php

namespace App\Http\Requests\Api\V1\Organizations;

use App\Enums\OrganizationRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrganizationMemberUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in(OrganizationRole::values())],
        ];
    }
}
