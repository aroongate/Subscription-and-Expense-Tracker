<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Subscription */
class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'vendor' => $this->vendor,
            'amount_minor' => $this->amount_minor,
            'currency_code' => $this->currency_code,
            'exchange_rate' => (float) $this->exchange_rate,
            'amount_base_minor' => $this->amount_base_minor,
            'billing_cycle' => $this->billing_cycle,
            'next_charge_at' => optional($this->next_charge_at)->toDateString(),
            'status' => $this->status,
            'notes' => $this->notes,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
