<?php

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ExpenseController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\OrganizationController;
use App\Http\Controllers\Api\V1\OrganizationMemberController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/token', [AuthTokenController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::delete('/auth/token', [AuthTokenController::class, 'destroy']);
        Route::get('/me', MeController::class);

        Route::get('/organizations', [OrganizationController::class, 'index']);
        Route::post('/organizations', [OrganizationController::class, 'store']);
        Route::post('/organizations/{organization}/switch', [OrganizationController::class, 'switchCurrent']);

        Route::middleware('org.context')->group(function (): void {
            Route::get('/organizations/{organization}/members', [OrganizationMemberController::class, 'index']);
            Route::post('/organizations/{organization}/members', [OrganizationMemberController::class, 'store']);
            Route::patch('/organizations/{organization}/members/{user}', [OrganizationMemberController::class, 'update']);
            Route::delete('/organizations/{organization}/members/{user}', [OrganizationMemberController::class, 'destroy']);

            Route::get('/categories', [CategoryController::class, 'index']);
            Route::post('/categories', [CategoryController::class, 'store']);
            Route::patch('/categories/{category}', [CategoryController::class, 'update']);
            Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

            Route::get('/subscriptions', [SubscriptionController::class, 'index']);
            Route::post('/subscriptions', [SubscriptionController::class, 'store']);
            Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'show']);
            Route::patch('/subscriptions/{subscription}', [SubscriptionController::class, 'update']);
            Route::delete('/subscriptions/{subscription}', [SubscriptionController::class, 'destroy']);

            Route::get('/expenses', [ExpenseController::class, 'index']);
            Route::post('/expenses', [ExpenseController::class, 'store']);
            Route::get('/expenses/{expense}', [ExpenseController::class, 'show']);
            Route::patch('/expenses/{expense}', [ExpenseController::class, 'update']);
            Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy']);

            Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
            Route::get('/dashboard/series', [DashboardController::class, 'series']);
        });
    });
});
