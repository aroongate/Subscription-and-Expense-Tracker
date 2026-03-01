<?php

namespace App\Http\Requests\Api\V1\Subscriptions;

use App\Enums\SubscriptionBillingCycle;
use App\Enums\SubscriptionStatus;
use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscriptionUpdateRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'vendor' => ['sometimes', 'nullable', 'string', 'max:255'],
            'amount_minor' => ['sometimes', 'integer', 'min:1'],
            'currency_code' => ['sometimes', 'string', 'size:3', 'alpha'],
            'exchange_rate' => ['sometimes', 'numeric', 'gt:0'],
            'billing_cycle' => ['sometimes', Rule::in(SubscriptionBillingCycle::values())],
            'next_charge_at' => ['sometimes', 'date'],
            'status' => ['sometimes', Rule::in(SubscriptionStatus::values())],
            'notes' => ['sometimes', 'nullable', 'string'],
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
