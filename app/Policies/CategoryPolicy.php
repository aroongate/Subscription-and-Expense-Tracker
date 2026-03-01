<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user, Organization $organization): bool
    {
        return $user->organizations()->where('organizations.id', $organization->id)->exists();
    }

    public function view(User $user, Category $category): bool
    {
        return $user->organizations()->where('organizations.id', $category->organization_id)->exists();
    }

    public function create(User $user, Organization $organization): bool
    {
        return $this->canManage($user, $organization);
    }

    public function update(User $user, Category $category): bool
    {
        $organization = $category->organization;

        if (! $organization instanceof Organization) {
            return false;
        }

        return $this->canManage($user, $organization);
    }

    public function delete(User $user, Category $category): bool
    {
        $organization = $category->organization;

        if (! $organization instanceof Organization) {
            return false;
        }

        return $this->canManage($user, $organization);
    }

    private function canManage(User $user, Organization $organization): bool
    {
        return $user->hasAnyOrganizationRole($organization, [
            OrganizationRole::Owner->value,
            OrganizationRole::Admin->value,
        ]);
    }
}
