<?php

namespace App\Http\Requests\Api\V1\Subscriptions;

use App\Enums\SubscriptionBillingCycle;
use App\Enums\SubscriptionStatus;
use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscriptionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('organization_id', $this->organizationId())),
            ],
            'name' => ['required', 'string', 'max:255'],
            'vendor' => ['nullable', 'string', 'max:255'],
            'amount_minor' => ['required', 'integer', 'min:1'],
            'currency_code' => ['required', 'string', 'size:3', 'alpha'],
            'exchange_rate' => ['nullable', 'numeric', 'gt:0'],
            'billing_cycle' => ['required', Rule::in(SubscriptionBillingCycle::values())],
            'next_charge_at' => ['required', 'date'],
            'status' => ['nullable', Rule::in(SubscriptionStatus::values())],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $currencyCode = (string) $this->input('currency_code', '');

        $this->merge([
            'currency_code' => strtoupper($currencyCode),
            'exchange_rate' => $this->input('exchange_rate', 1),
            'status' => $this->input('status', SubscriptionStatus::Active->value),
        ]);
    }

    private function organizationId(): ?int
    {
        /** @var Organization|null $organization */
        $organization = $this->attributes->get('organization');

        return $organization?->id;
    }
}
