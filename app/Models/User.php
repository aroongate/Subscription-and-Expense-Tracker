<?php

namespace App\Models;

use App\Enums\OrganizationRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function ownedOrganizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'owner_user_id');
    }

    public function createdExpenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'created_by_user_id');
    }

    public function roleInOrganization(Organization|int $organization): ?OrganizationRole
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        $org = $this->organizations()
            ->where('organizations.id', $organizationId)
            ->first();

        if (! $org || ! isset($org->pivot->role)) {
            return null;
        }

        return OrganizationRole::tryFrom($org->pivot->role);
    }

    public function hasAnyOrganizationRole(Organization|int $organization, array $roles): bool
    {
        $role = $this->roleInOrganization($organization);

        if (! $role) {
            return false;
        }

        return in_array($role->value, $roles, true);
    }
}
