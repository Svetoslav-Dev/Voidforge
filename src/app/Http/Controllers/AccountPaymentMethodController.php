<?php

namespace App\Http\Controllers;

use App\Models\SavedPaymentMethod;
use App\Services\StripePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountPaymentMethodController extends Controller
{
    /**
     * Display the saved card page.
     */
    public function index(Request $request, StripePaymentService $stripe): View|RedirectResponse
    {
        if ($request->query('stripe') === 'success' && filled($request->query('session_id'))) {
            $stripe->completePaymentMethodSetup($request->user(), (string) $request->query('session_id'));

            return redirect()
                ->route('account.payment-methods.index')
                ->with('status', 'Card added successfully.');
        }

        if ($request->query('stripe') === 'cancelled') {
            return redirect()
                ->route('account.payment-methods.index')
                ->with('status', 'Card setup was cancelled.');
        }

        return view('account.payment-methods', [
            'paymentMethods' => $request->user()?->savedPaymentMethods()->get() ?? collect(),
            'stripeConfigured' => $stripe->isConfigured(),
        ]);
    }

    /**
     * Redirect the user to Stripe-hosted card setup.
     */
    public function store(Request $request, StripePaymentService $stripe): RedirectResponse
    {
        if (! $stripe->isConfigured()) {
            return redirect()
                ->route('account.payment-methods.index')
                ->withErrors([
                    'payment_method' => 'Stripe is not configured for this environment yet.',
                ]);
        }

        $setupUrl = $stripe->startPaymentMethodSetup($request->user());

        return redirect()->away($setupUrl);
    }

    /**
     * Mark a saved card as the default one for the account.
     */
    public function makeDefault(Request $request, SavedPaymentMethod $paymentMethod): RedirectResponse|JsonResponse
    {
        $this->ensureOwnership($request, $paymentMethod);

        $request->user()->savedPaymentMethods()->update(['is_default' => false]);

        $paymentMethod->update(['is_default' => true]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'status' => 'Default card updated.',
                'payment_method_id' => $paymentMethod->id,
            ]);
        }

        return redirect()
            ->route('account.payment-methods.index')
            ->with('status', 'Default card updated.');
    }

    /**
     * Remove a saved card from the account.
     */
    public function destroy(Request $request, SavedPaymentMethod $paymentMethod): RedirectResponse
    {
        $this->ensureOwnership($request, $paymentMethod);

        $user = $request->user();
        $wasDefault = $paymentMethod->is_default;

        $paymentMethod->delete();

        if ($wasDefault) {
            $user->savedPaymentMethods()
                ->oldest('id')
                ->limit(1)
                ->update(['is_default' => true]);
        }

        return redirect()
            ->route('account.payment-methods.index')
            ->with('status', 'Saved card removed.');
    }

    /**
     * Ensure the current user owns the saved card.
     */
    protected function ensureOwnership(Request $request, SavedPaymentMethod $paymentMethod): void
    {
        abort_unless((int) $paymentMethod->user_id === (int) $request->user()?->id, 404);
    }
}
