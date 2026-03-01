<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Models\Expense;
use App\Models\Organization;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardMetricsService
{
    public function summary(Organization $organization, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $queryRange = [
            $from?->startOfDay(),
            $to?->endOfDay(),
        ];

        $expensesQuery = Expense::query()
            ->where('organization_id', $organization->id);

        if ($queryRange[0] && $queryRange[1]) {
            $expensesQuery->whereBetween('spent_at', $queryRange);
        }

        $subscriptionsQuery = Subscription::query()
            ->where('organization_id', $organization->id)
            ->where('status', SubscriptionStatus::Active->value);

        $expensesTotal = (int) $expensesQuery->sum('amount_base_minor');
        $subscriptionsTotal = (int) $subscriptionsQuery->sum('amount_base_minor');

        $upcomingQuery = Subscription::query()
            ->where('organization_id', $organization->id)
            ->where('status', SubscriptionStatus::Active->value)
            ->whereBetween('next_charge_at', [now()->startOfDay(), now()->addDays(30)->endOfDay()]);

        return [
            'base_currency_code' => $organization->base_currency_code,
            'totals' => [
                'expenses_base_minor' => $expensesTotal,
                'subscriptions_base_minor' => $subscriptionsTotal,
                'combined_base_minor' => $expensesTotal + $subscriptionsTotal,
            ],
            'counts' => [
                'expenses' => (int) $expensesQuery->count(),
                'subscriptions' => (int) $subscriptionsQuery->count(),
                'upcoming_charges' => (int) $upcomingQuery->count(),
            ],
            'upcoming_charges_base_minor' => (int) $upcomingQuery->sum('amount_base_minor'),
        ];
    }

    public function monthlySeries(Organization $organization, Carbon $from, Carbon $to): array
    {
        $from = $from->copy()->startOfMonth();
        $to = $to->copy()->endOfMonth();

        $months = $this->buildMonths($from, $to);

        $expenseMonthExpression = $this->monthExpression('spent_at');

        $expenseRows = Expense::query()
            ->selectRaw("{$expenseMonthExpression} as month")
            ->selectRaw('SUM(amount_base_minor) as total')
            ->where('organization_id', $organization->id)
            ->whereBetween('spent_at', [$from->toDateString(), $to->toDateString()])
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $subscriptionMonthExpression = $this->monthExpression('next_charge_at');

        $subscriptionRows = Subscription::query()
            ->selectRaw("{$subscriptionMonthExpression} as month")
            ->selectRaw('SUM(amount_base_minor) as total')
            ->where('organization_id', $organization->id)
            ->whereBetween('next_charge_at', [$from->toDateString(), $to->toDateString()])
            ->where('status', SubscriptionStatus::Active->value)
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        return [
            'base_currency_code' => $organization->base_currency_code,
            'series' => $months->map(function (string $month) use ($expenseRows, $subscriptionRows): array {
                return [
                    'month' => $month,
                    'expenses_base_minor' => (int) ($expenseRows[$month]->total ?? 0),
                    'subscriptions_base_minor' => (int) ($subscriptionRows[$month]->total ?? 0),
                ];
            })->all(),
        ];
    }

    private function buildMonths(Carbon $from, Carbon $to): Collection
    {
        $months = collect();
        $cursor = $from->copy();

        while ($cursor <= $to) {
            $months->push($cursor->format('Y-m'));
            $cursor->addMonth();
        }

        return $months;
    }

    private function monthExpression(string $column): string
    {
        $driver = DB::connection()->getDriverName();

        return match ($driver) {
            'pgsql' => "to_char({$column}, 'YYYY-MM')",
            'mysql' => "DATE_FORMAT({$column}, '%Y-%m')",
            default => "strftime('%Y-%m', {$column})",
        };
    }
}
