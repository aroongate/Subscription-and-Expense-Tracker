<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    public function viewAny(User $user): bool
    {
        return ! empty($user->id);
    }

    public function view(User $user, Organization $organization): bool
    {
        return $user->organizations()->where('organizations.id', $organization->id)->exists();
    }

    public function create(User $user): bool
    {
        return ! empty($user->id);
    }

    public function update(User $user, Organization $organization): bool
    {
        return $this->hasRole($user, $organization, [OrganizationRole::Owner, OrganizationRole::Admin]);
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $this->hasRole($user, $organization, [OrganizationRole::Owner]);
    }

    public function manageMembers(User $user, Organization $organization): bool
    {
        return $this->hasRole($user, $organization, [OrganizationRole::Owner, OrganizationRole::Admin]);
    }

    public function transferOwnership(User $user, Organization $organization): bool
    {
        return $this->hasRole($user, $organization, [OrganizationRole::Owner]);
    }

    private function hasRole(User $user, Organization $organization, array $roles): bool
    {
        $roleValues = array_map(static fn (OrganizationRole $role): string => $role->value, $roles);

        return $user->hasAnyOrganizationRole($organization, $roleValues);
    }
}
