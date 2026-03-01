<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\LocaleController;
use App\Http\Controllers\Web\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/expenses', [PageController::class, 'expenses'])->name('expenses.index');
    Route::get('/expenses/form', [PageController::class, 'expenseForm'])->name('expenses.form');
    Route::get('/subscriptions', [PageController::class, 'subscriptions'])->name('subscriptions.index');
    Route::get('/subscriptions/form', [PageController::class, 'subscriptionForm'])->name('subscriptions.form');
    Route::get('/categories', [PageController::class, 'categories'])->name('categories.index');
    Route::get('/settings/organization', [PageController::class, 'settingsOrganization'])->name('settings.organization');
});
