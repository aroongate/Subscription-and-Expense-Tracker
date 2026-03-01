<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Organization;
use App\Models\Subscription;
use App\Policies\CategoryPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\OrganizationPolicy;
use App\Policies\SubscriptionPolicy;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        Gate::policy(Organization::class, OrganizationPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Subscription::class, SubscriptionPolicy::class);
        Gate::policy(Expense::class, ExpensePolicy::class);
    }
}
