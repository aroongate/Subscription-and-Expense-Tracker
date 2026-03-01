<?php

namespace App\Models;

use App\Enums\OrganizationRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_user_id',
        'base_currency_code',
    ];

    protected function casts(): array
    {
        return [
            'base_currency_code' => 'string',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function roleForUser(User $user): ?OrganizationRole
    {
        $member = $this->users()
            ->where('user_id', $user->id)
            ->first();

        if (! $member || ! isset($member->pivot->role)) {
            return null;
        }

        return OrganizationRole::tryFrom($member->pivot->role);
    }
}
