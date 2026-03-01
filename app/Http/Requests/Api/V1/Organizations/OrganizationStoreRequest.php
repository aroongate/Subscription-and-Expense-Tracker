<?php

namespace App\Http\Requests\Api\V1\Organizations;

use Illuminate\Foundation\Http\FormRequest;

class OrganizationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'base_currency_code' => ['nullable', 'string', 'size:3', 'alpha'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('base_currency_code')) {
            $this->merge([
                'base_currency_code' => strtoupper((string) $this->input('base_currency_code')),
            ]);
        }
    }
}
