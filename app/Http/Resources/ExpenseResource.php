<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Expense */
class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'category_id' => $this->category_id,
            'created_by_user_id' => $this->created_by_user_id,
            'title' => $this->title,
            'amount_minor' => $this->amount_minor,
            'currency_code' => $this->currency_code,
            'exchange_rate' => (float) $this->exchange_rate,
            'amount_base_minor' => $this->amount_base_minor,
            'spent_at' => optional($this->spent_at)->toDateString(),
            'note' => $this->note,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'author' => UserResource::make($this->whenLoaded('author')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
