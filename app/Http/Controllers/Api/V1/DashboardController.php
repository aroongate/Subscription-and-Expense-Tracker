<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ResolvesOrganizationContext;
use App\Http\Requests\Api\V1\DashboardFilterRequest;
use App\Services\DashboardMetricsService;
use Carbon\Carbon;

class DashboardController extends ApiController
{
    use ResolvesOrganizationContext;

    public function __construct(private readonly DashboardMetricsService $dashboardMetricsService) {}

    public function summary(DashboardFilterRequest $request)
    {
        $organization = $this->organizationFromRequest($request);

        $from = $request->filled('from') ? Carbon::parse((string) $request->string('from')) : null;
        $to = $request->filled('to') ? Carbon::parse((string) $request->string('to')) : null;

        return $this->success(
            $this->dashboardMetricsService->summary($organization, $from, $to)
        );
    }

    public function series(DashboardFilterRequest $request)
    {
        $organization = $this->organizationFromRequest($request);

        $from = $request->filled('from')
            ? Carbon::parse((string) $request->string('from'))
            : now()->subMonths(5)->startOfMonth();

        $to = $request->filled('to')
            ? Carbon::parse((string) $request->string('to'))
            : now()->endOfMonth();

        return $this->success(
            $this->dashboardMetricsService->monthlySeries($organization, $from, $to)
        );
    }
}
