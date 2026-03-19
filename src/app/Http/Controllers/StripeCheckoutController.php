<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\StripePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StripeCheckoutController extends Controller
{
    /**
     * Redirect the customer to Stripe Checkout.
     */
    public function store(Request $request, Order $order, StripePaymentService $stripe): RedirectResponse
    {
        $this->abortUnlessCanAccessOrder($request, $order);

        if ($order->status === 'paid') {
            return $this->redirectToCheckoutStep($order)
                ->with('status', 'This order has already been paid.');
        }

        if (! $stripe->isConfigured()) {
            return $this->redirectToCheckoutStep($order)
                ->withErrors([
                    'payment' => 'Stripe is not configured for this environment yet.',
                ]);
        }

        $checkoutUrl = $stripe->startCheckout($order->loadMissing('items'));

        return redirect()->away($checkoutUrl);
    }

    /**
     * Handle Stripe webhook notifications.
     */
    public function webhook(Request $request, StripePaymentService $stripe): Response
    {
        $stripe->handleWebhook(
            $request->getContent(),
            $request->header('Stripe-Signature')
        );

        return response('', 200);
    }

    /**
     * Ensure the current visitor can access the order.
     */
    private function abortUnlessCanAccessOrder(Request $request, Order $order): void
    {
        if (
            $request->user()
            && $order->user_id !== null
            && $order->user_id !== $request->user()->id
        ) {
            throw new NotFoundHttpException();
        }

        if (
            ! $request->user()
            && $order->customer_email !== $request->session()->get('checkout.last_order_email')
        ) {
            throw new NotFoundHttpException();
        }
    }

    private function redirectToCheckoutStep(Order $order): RedirectResponse
    {
        return $order->placed_at
            ? redirect()->route('checkout.complete')
            : redirect()->route('checkout.index');
    }
}
