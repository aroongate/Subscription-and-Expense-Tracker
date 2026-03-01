<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ResolvesOrganizationContext;
use App\Http\Requests\Api\V1\Subscriptions\SubscriptionStoreRequest;
use App\Http\Requests\Api\V1\Subscriptions\SubscriptionUpdateRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Services\MoneyConversionService;
use Illuminate\Http\Request;

class SubscriptionController extends ApiController
{
    use ResolvesOrganizationContext;

    public function __construct(private readonly MoneyConversionService $moneyConversionService) {}

    public function index(Request $request)
    {
        $organization = $this->organizationFromRequest($request);
        $this->authorize('viewAny', [Subscription::class, $organization]);

        $query = Subscription::query()
            ->where('organization_id', $organization->id)
            ->with('category')
            ->orderByDesc('next_charge_at');

        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('next_charge_at', [$request->date('from'), $request->date('to')]);
        }

        return $this->success(SubscriptionResource::collection($query->get()));
    }

    public function show(Request $request, Subscription $subscription)
    {
        $organization = $this->organizationFromRequest($request);

        if ($subscription->organization_id !== $organization->id) {
            return $this->error('Subscription not found in current organization.', 404);
        }

        $this->authorize('view', $subscription);

        return $this->success(SubscriptionResource::make($subscription->load('category')));
    }

    public function store(SubscriptionStoreRequest $request)
    {
        $organization = $this->organizationFromRequest($request);
        $this->authorize('create', [Subscription::class, $organization]);

        $validated = $request->validated();

        $amountBaseMinor = $this->moneyConversionService->toBaseMinor(
            amountMinor: (int) $validated['amount_minor'],
            currencyCode: (string) $validated['currency_code'],
            baseCurrencyCode: $organization->base_currency_code,
            exchangeRate: (float) $validated['exchange_rate'],
        );

        $subscription = Subscription::query()->create([
            ...$validated,
            'organization_id' => $organization->id,
            'amount_base_minor' => $amountBaseMinor,
        ]);

        return $this->success(
            SubscriptionResource::make($subscription->load('category')),
            status: 201
        );
    }

    public function update(SubscriptionUpdateRequest $request, Subscription $subscription)
    {
        $organization = $this->organizationFromRequest($request);

        if ($subscription->organization_id !== $organization->id) {
            return $this->error('Subscription not found in current organization.', 404);
        }

        $this->authorize('update', $subscription);

        $validated = $request->validated();

        $amountMinor = (int) ($validated['amount_minor'] ?? $subscription->amount_minor);
        $currencyCode = (string) ($validated['currency_code'] ?? $subscription->currency_code);
        $exchangeRate = (float) ($validated['exchange_rate'] ?? $subscription->exchange_rate);

        $validated['amount_base_minor'] = $this->moneyConversionService->toBaseMinor(
            amountMinor: $amountMinor,
            currencyCode: $currencyCode,
            baseCurrencyCode: $organization->base_currency_code,
            exchangeRate: $exchangeRate,
        );

        $subscription->update($validated);

        return $this->success(SubscriptionResource::make($subscription->load('category')));
    }

    public function destroy(Request $request, Subscription $subscription)
    {
        $organization = $this->organizationFromRequest($request);

        if ($subscription->organization_id !== $organization->id) {
            return $this->error('Subscription not found in current organization.', 404);
        }

        $this->authorize('delete', $subscription);

        $subscription->delete();

        return $this->success([
            'deleted_id' => $subscription->id,
        ]);
    }
}
