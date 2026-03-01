<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrganizationRole;
use App\Http\Requests\Api\V1\Organizations\OrganizationMemberStoreRequest;
use App\Http\Requests\Api\V1\Organizations\OrganizationMemberUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizationMemberController extends ApiController
{
    public function index(Request $request, Organization $organization)
    {
        $this->authorize('view', $organization);

        $members = $organization->users()->orderBy('name')->get();

        return $this->success(UserResource::collection($members));
    }

    public function store(OrganizationMemberStoreRequest $request, Organization $organization)
    {
        $this->authorize('manageMembers', $organization);

        $actorRole = $request->user()->roleInOrganization($organization);
        $targetRole = OrganizationRole::from((string) $request->string('role'));

        if ($targetRole === OrganizationRole::Owner && $actorRole !== OrganizationRole::Owner) {
            return $this->error('Only owner can assign owner role.', 403);
        }

        $member = User::query()->where('email', (string) $request->string('email'))->firstOrFail();

        if ($organization->users()->where('user_id', $member->id)->exists()) {
            return $this->error('User is already a member of this organization.', 422, [
                'email' => ['User is already in this organization.'],
            ]);
        }

        DB::transaction(function () use ($organization, $member, $targetRole): void {
            $organization->users()->attach($member->id, [
                'role' => $targetRole->value,
            ]);

            if ($targetRole === OrganizationRole::Owner) {
                $organization->update(['owner_user_id' => $member->id]);
            }
        });

        $createdMember = $organization->users()->where('user_id', $member->id)->first();

        return $this->success(UserResource::make($createdMember), status: 201);
    }

    public function update(
        OrganizationMemberUpdateRequest $request,
        Organization $organization,
        User $user
    ) {
        $this->authorize('manageMembers', $organization);

        if (! $organization->users()->where('user_id', $user->id)->exists()) {
            return $this->error('User is not a member of this organization.', 404);
        }

        $actorRole = $request->user()->roleInOrganization($organization);
        $targetRole = OrganizationRole::from((string) $request->string('role'));

        if ($targetRole === OrganizationRole::Owner && $actorRole !== OrganizationRole::Owner) {
            return $this->error('Only owner can transfer ownership.', 403);
        }

        if ($actorRole === OrganizationRole::Admin && $organization->owner_user_id === $user->id) {
            return $this->error('Admin cannot update owner role.', 403);
        }

        DB::transaction(function () use ($organization, $user, $targetRole): void {
            if ($targetRole === OrganizationRole::Owner) {
                $organization->users()->updateExistingPivot($organization->owner_user_id, [
                    'role' => OrganizationRole::Admin->value,
                ]);

                $organization->update(['owner_user_id' => $user->id]);
            }

            $organization->users()->updateExistingPivot($user->id, [
                'role' => $targetRole->value,
                'updated_at' => now(),
            ]);
        });

        $member = $organization->users()->where('user_id', $user->id)->first();

        return $this->success(UserResource::make($member));
    }

    public function destroy(Request $request, Organization $organization, User $user)
    {
        $this->authorize('manageMembers', $organization);

        if ($organization->owner_user_id === $user->id) {
            return $this->error('Owner cannot be removed from the organization.', 422);
        }

        if (! $organization->users()->where('user_id', $user->id)->exists()) {
            return $this->error('User is not a member of this organization.', 404);
        }

        $organization->users()->detach($user->id);

        return $this->success([
            'removed_user_id' => $user->id,
        ]);
    }
}
