<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use App\Models\Organization;
use Illuminate\Http\Request;

trait ResolvesOrganizationContext
{
    protected function organizationFromRequest(Request $request): Organization
    {
        /** @var Organization $organization */
        $organization = $request->attributes->get('organization');

        return $organization;
    }
}
