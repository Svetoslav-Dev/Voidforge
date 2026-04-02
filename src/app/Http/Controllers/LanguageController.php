<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    private const SUPPORTED = ['en', 'bg'];

    public function switch(Request $request): RedirectResponse
    {
        $locale = $request->input('locale');

        if (!in_array($locale, self::SUPPORTED, true)) {
            return back();
        }

        if ($request->user()) {
            $request->user()->update(['preferred_locale' => $locale]);
        } else {
            $request->session()->put('preferred_locale', $locale);
        }

        return back();
    }
}
