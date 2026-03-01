<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ResolvesOrganizationContext;
use App\Http\Requests\Api\V1\Expenses\ExpenseStoreRequest;
use App\Http\Requests\Api\V1\Expenses\ExpenseUpdateRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Services\MoneyConversionService;
use Illuminate\Http\Request;

class ExpenseController extends ApiController
{
    use ResolvesOrganizationContext;

    public function __construct(private readonly MoneyConversionService $moneyConversionService) {}

    public function index(Request $request)
    {
        $organization = $this->organizationFromRequest($request);
        $this->authorize('viewAny', [Expense::class, $organization]);

        $query = Expense::query()
            ->where('organization_id', $organization->id)
            ->with(['category', 'author'])
            ->orderByDesc('spent_at');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('spent_at', [$request->date('from'), $request->date('to')]);
        }

        return $this->success(ExpenseResource::collection($query->get()));
    }

    public function show(Request $request, Expense $expense)
    {
        $organization = $this->organizationFromRequest($request);

        if ($expense->organization_id !== $organization->id) {
            return $this->error('Expense not found in current organization.', 404);
        }

        $this->authorize('view', $expense);

        return $this->success(ExpenseResource::make($expense->load(['category', 'author'])));
    }

    public function store(ExpenseStoreRequest $request)
    {
        $organization = $this->organizationFromRequest($request);
        $this->authorize('create', [Expense::class, $organization]);

        $validated = $request->validated();

        $amountBaseMinor = $this->moneyConversionService->toBaseMinor(
            amountMinor: (int) $validated['amount_minor'],
            currencyCode: (string) $validated['currency_code'],
            baseCurrencyCode: $organization->base_currency_code,
            exchangeRate: (float) $validated['exchange_rate'],
        );

        $expense = Expense::query()->create([
            ...$validated,
            'organization_id' => $organization->id,
            'created_by_user_id' => $request->user()->id,
            'amount_base_minor' => $amountBaseMinor,
        ]);

        return $this->success(ExpenseResource::make($expense->load(['category', 'author'])), status: 201);
    }

    public function update(ExpenseUpdateRequest $request, Expense $expense)
    {
        $organization = $this->organizationFromRequest($request);

        if ($expense->organization_id !== $organization->id) {
            return $this->error('Expense not found in current organization.', 404);
        }

        $this->authorize('update', $expense);

        $validated = $request->validated();

        $amountMinor = (int) ($validated['amount_minor'] ?? $expense->amount_minor);
        $currencyCode = (string) ($validated['currency_code'] ?? $expense->currency_code);
        $exchangeRate = (float) ($validated['exchange_rate'] ?? $expense->exchange_rate);

        $validated['amount_base_minor'] = $this->moneyConversionService->toBaseMinor(
            amountMinor: $amountMinor,
            currencyCode: $currencyCode,
            baseCurrencyCode: $organization->base_currency_code,
            exchangeRate: $exchangeRate,
        );

        $expense->update($validated);

        return $this->success(ExpenseResource::make($expense->load(['category', 'author'])));
    }

    public function destroy(Request $request, Expense $expense)
    {
        $organization = $this->organizationFromRequest($request);

        if ($expense->organization_id !== $organization->id) {
            return $this->error('Expense not found in current organization.', 404);
        }

        $this->authorize('delete', $expense);

        $expense->delete();

        return $this->success([
            'deleted_id' => $expense->id,
        ]);
    }
}
