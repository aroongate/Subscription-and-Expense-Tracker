<?php

namespace App\Http\Middleware;

use App\Http\Resources\OrganizationResource;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => fn () => $request->user()
                    ? [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                        'email' => $request->user()->email,
                    ]
                    : null,
            ],
            'organizations' => fn () => $request->user()
                ? OrganizationResource::collection($request->user()->organizations()->orderBy('name')->get())
                : [],
            'currentOrganizationId' => fn () => $request->session()->get('current_organization_id'),
            'locale' => fn () => app()->getLocale(),
            'availableLocales' => fn () => collect(config('app.supported_locales', ['en']))
                ->map(fn (string $locale): array => [
                    'code' => $locale,
                    'label' => match ($locale) {
                        'ru' => 'Русский',
                        'en' => 'English',
                        default => strtoupper($locale),
                    },
                ])
                ->values()
                ->all(),
            'csrf_token' => fn () => csrf_token(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
