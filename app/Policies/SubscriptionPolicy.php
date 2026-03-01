<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy
{
    public function viewAny(User $user, Organization $organization): bool
    {
        return $user->organizations()->where('organizations.id', $organization->id)->exists();
    }

    public function view(User $user, Subscription $subscription): bool
    {
        return $user->organizations()->where('organizations.id', $subscription->organization_id)->exists();
    }

    public function create(User $user, Organization $organization): bool
    {
        return $this->canManageFinance($user, $organization);
    }

    public function update(User $user, Subscription $subscription): bool
    {
        $organization = $subscription->organization;

        if (! $organization instanceof Organization) {
            return false;
        }

        return $this->canManageFinance($user, $organization);
    }

    public function delete(User $user, Subscription $subscription): bool
    {
        $organization = $subscription->organization;

        if (! $organization instanceof Organization) {
            return false;
        }

        return $this->canManageFinance($user, $organization);
    }

    private function canManageFinance(User $user, Organization $organization): bool
    {
        return $user->hasAnyOrganizationRole($organization, [
            OrganizationRole::Owner->value,
            OrganizationRole::Admin->value,
            OrganizationRole::Member->value,
        ]);
    }
}
