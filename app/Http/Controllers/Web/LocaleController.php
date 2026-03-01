<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'locale' => [
                'required',
                'string',
                Rule::in(config('app.supported_locales', ['en'])),
            ],
        ]);

        $locale = (string) $validated['locale'];

        if ($request->hasSession()) {
            $request->session()->put('locale', $locale);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'data' => [
                    'locale' => $locale,
                ],
            ]);
        }

        return back();
    }
}
