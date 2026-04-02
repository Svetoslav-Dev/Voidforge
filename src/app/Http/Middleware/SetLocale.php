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
        $locale = $this->resolveLocale($request);

        App::setLocale($locale);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $supported = ['en', 'bg'];

        if ($request->user() && $request->user()->preferred_locale) {
            return $request->user()->preferred_locale;
        }

        if ($request->session()->has('preferred_locale')) {
            return $request->session()->get('preferred_locale');
        }

        $accept = $request->header('Accept-Language', '');

        foreach (explode(',', $accept) as $part) {
            $tag = strtolower(trim(explode(';', $part)[0]));
            $primary = explode('-', $tag)[0];

            if (in_array($primary, $supported, true)) {
                return $primary;
            }
        }

        return 'en';
    }
}
