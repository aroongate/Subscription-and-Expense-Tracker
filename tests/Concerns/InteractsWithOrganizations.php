<?php

namespace Tests\Concerns;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;

trait InteractsWithOrganizations
{
    protected function createOrganizationWithMember(User $owner, ?User $member = null, string $memberRole = OrganizationRole::Member->value): Organization
    {
        $organization = Organization::factory()->create([
            'owner_user_id' => $owner->id,
        ]);

        $organization->users()->attach($owner->id, [
            'role' => OrganizationRole::Owner->value,
        ]);

        if ($member) {
            $organization->users()->attach($member->id, [
                'role' => $memberRole,
            ]);
        }

        return $organization;
    }

    protected function orgHeader(Organization $organization): array
    {
        return [
            'X-Org-Id' => (string) $organization->id,
        ];
    }
}
