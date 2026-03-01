<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = config('app.supported_locales', [config('app.locale')]);
        $locale = null;

        $headerLocale = $request->header('X-Locale');

        if (is_string($headerLocale) && in_array($headerLocale, $supportedLocales, true)) {
            $locale = $headerLocale;
        }

        if (! $locale && $request->hasSession()) {
            $sessionLocale = $request->session()->get('locale');

            if (is_string($sessionLocale) && in_array($sessionLocale, $supportedLocales, true)) {
                $locale = $sessionLocale;
            }
        }

        if (! $locale) {
            $preferredLocale = $request->getPreferredLanguage($supportedLocales);

            if (is_string($preferredLocale) && in_array($preferredLocale, $supportedLocales, true)) {
                $locale = $preferredLocale;
            }
        }

        $locale ??= config('app.locale', 'en');

        App::setLocale($locale);

        if ($request->hasSession()) {
            $request->session()->put('locale', $locale);
        }

        return $next($request);
    }
}
