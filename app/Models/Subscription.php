<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'category_id',
        'name',
        'vendor',
        'amount_minor',
        'currency_code',
        'exchange_rate',
        'amount_base_minor',
        'billing_cycle',
        'next_charge_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'next_charge_at' => 'date',
            'amount_minor' => 'integer',
            'amount_base_minor' => 'integer',
            'exchange_rate' => 'decimal:6',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
}
