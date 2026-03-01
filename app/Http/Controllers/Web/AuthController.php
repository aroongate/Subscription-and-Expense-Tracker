<?php

namespace App\Http\Controllers\Web;

use App\Enums\OrganizationRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Auth\LoginRequest;
use App\Http\Requests\Web\Auth\RegisterRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request): User {
            $user = User::query()->create([
                'name' => (string) $request->string('name'),
                'email' => (string) $request->string('email'),
                'password' => Hash::make((string) $request->string('password')),
            ]);

            $organization = Organization::query()->create([
                'name' => sprintf("%s's Workspace", $user->name),
                'owner_user_id' => $user->id,
                'base_currency_code' => 'RUB',
            ]);

            $organization->users()->attach($user->id, [
                'role' => OrganizationRole::Owner->value,
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
