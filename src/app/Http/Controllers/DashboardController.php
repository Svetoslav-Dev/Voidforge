<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the account page for the current user.
     */
    public function __invoke(): View
    {
        $user = auth()->user();

        return view('dashboard', [
            'defaultShippingAddress' => $user?->shippingAddresses()
                ->where('is_default', true)
                ->first(),
            'defaultSavedCard' => $user?->savedPaymentMethods()
                ->where('is_default', true)
                ->first(),
        ]);
    }

    /**
     * Soft delete the current account and end the session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('products.index');
    }
}
