<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CookieConsentController extends Controller
{
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'consent' => ['nullable', 'in:all,essential'],
            'optional' => ['nullable', 'boolean'],
            'return_to' => ['nullable', 'string', 'max:2048'],
        ]);

        $consent = $validated['consent']
            ?? ($request->boolean('optional') ? 'all' : 'essential');

        $request->session()->put('voidforgestore_cookie_consent', $consent);

        $responseCookie = cookie()->make(
            'voidforgestore_cookie_consent',
            $consent,
            60 * 24 * 365,
            '/',
            null,
            $request->isSecure(),
            false,
            false,
            'lax',
        );

        if ($request->expectsJson()) {
            return response()->json(['ok' => true])->withCookie($responseCookie);
        }

        return redirect($this->sanitizeReturnTo($validated['return_to'] ?? null, $request))
            ->withCookie($responseCookie);
    }

    private function sanitizeReturnTo(?string $returnTo, Request $request): string
    {
        if (! is_string($returnTo) || trim($returnTo) === '') {
            return route('products.index');
        }

        $parsed = parse_url($returnTo);

        if ($parsed === false) {
            return route('products.index');
        }

        $host = $parsed['host'] ?? null;

        if ($host !== null && $host !== $request->getHost()) {
            return route('products.index');
        }

        $path = $parsed['path'] ?? '/';
        $query = isset($parsed['query']) ? '?'.$parsed['query'] : '';

        if (! str_starts_with($path, '/')) {
            $path = '/'.$path;
        }

        return $path.$query;
    }
}
