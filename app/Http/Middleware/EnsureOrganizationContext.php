<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $orgId = $request->header('X-Org-Id');

        if (! $orgId) {
            return response()->json([
                'message' => 'Organization ID is required',
            ], 400);
        }

        if (! is_numeric($orgId)) {
            return response()->json([
                'message' => 'Organization ID must be a valid integer.',
            ], 400);
        }

        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $organization = Organization::query()->find((int) $orgId);

        if (! $organization) {
            return response()->json([
                'message' => 'Organization not found.',
            ], 404);
        }

        $isMember = $user->organizations()
            ->where('organizations.id', $organization->id)
            ->exists();

        if (! $isMember) {
            return response()->json([
                'message' => 'You are not a member of this organization.',
            ], 403);
        }

        $routeOrganization = $request->route('organization');

        if ($routeOrganization && (int) $routeOrganization->id !== (int) $organization->id) {
            return response()->json([
                'message' => 'X-Org-Id does not match route organization.',
            ], 400);
        }

        $request->attributes->set('organization', $organization);

        return $next($request);
    }
}
