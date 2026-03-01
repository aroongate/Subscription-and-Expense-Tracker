<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrganizationRole;
use App\Http\Requests\Api\V1\Organizations\OrganizationStoreRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizationController extends ApiController
{
    public function index(Request $request)
    {
        $organizations = $request->user()
            ->organizations()
            ->orderBy('organizations.name')
            ->get();

        return $this->success(OrganizationResource::collection($organizations));
    }

    public function store(OrganizationStoreRequest $request)
    {
        $this->authorize('create', Organization::class);

        $user = $request->user();

        $organization = DB::transaction(function () use ($request, $user): Organization {
            $organization = Organization::query()->create([
                'name' => (string) $request->string('name'),
                'owner_user_id' => $user->id,
                'base_currency_code' => (string) $request->string('base_currency_code', 'RUB'),
            ]);

            $organization->users()->attach($user->id, [
                'role' => OrganizationRole::Owner->value,
            ]);

            return $organization;
        });

        return $this->success(OrganizationResource::make($organization->load('users')), status: 201);
    }

    public function switchCurrent(Request $request, Organization $organization)
    {
        $this->authorize('view', $organization);

        $sessionUpdated = false;

        if ($request->hasSession()) {
            $request->session()->put('current_organization_id', $organization->id);
            $sessionUpdated = true;
        }

        return $this->success([
            'current_organization_id' => $organization->id,
            'session_updated' => $sessionUpdated,
            'organization' => OrganizationResource::make($organization->load('users')),
        ]);
    }
}
