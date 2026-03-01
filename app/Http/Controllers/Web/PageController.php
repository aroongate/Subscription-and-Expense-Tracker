<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function dashboard(): Response
    {
        return Inertia::render('Dashboard/Index');
    }

    public function expenses(): Response
    {
        return Inertia::render('Expenses/Index');
    }

    public function expenseForm(): Response
    {
        return Inertia::render('Expenses/Form');
    }

    public function subscriptions(): Response
    {
        return Inertia::render('Subscriptions/Index');
    }

    public function subscriptionForm(): Response
    {
        return Inertia::render('Subscriptions/Form');
    }

    public function categories(): Response
    {
        return Inertia::render('Categories/Index');
    }

    public function settingsOrganization(): Response
    {
        return Inertia::render('Settings/Organization');
    }
}
