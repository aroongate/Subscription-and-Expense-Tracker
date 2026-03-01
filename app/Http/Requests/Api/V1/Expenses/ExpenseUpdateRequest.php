<?php

namespace App\Http\Requests\Api\V1\Expenses;

use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('organization_id', $this->organizationId())),
            ],
            'title' => ['sometimes', 'string', 'max:255'],
            'amount_minor' => ['sometimes', 'integer', 'min:1'],
            'currency_code' => ['sometimes', 'string', 'size:3', 'alpha'],
            'exchange_rate' => ['sometimes', 'numeric', 'gt:0'],
            'spent_at' => ['sometimes', 'date'],
            'note' => ['sometimes', 'nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('currency_code')) {
            $this->merge([
                'currency_code' => strtoupper((string) $this->input('currency_code')),
            ]);
        }
    }

    private function organizationId(): ?int
    {
        /** @var Organization|null $organization */
        $organization = $this->attributes->get('organization');

        return $organization?->id;
    }
}
