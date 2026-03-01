<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Organization */
class OrganizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'base_currency_code' => $this->base_currency_code,
            'owner_user_id' => $this->owner_user_id,
            'role' => $this->when(
                data_get($this->resource, 'pivot') !== null,
                fn (): ?string => data_get($this->resource, 'pivot.role')
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
