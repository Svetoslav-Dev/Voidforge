<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PayPalPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PayPalCheckoutController extends Controller
{
    /**
     * Redirect the customer to PayPal approval.
     */
    public function store(Request $request, Order $order, PayPalPaymentService $paypal): RedirectResponse
    {
        $this->abortUnlessCanAccessOrder($request, $order);

        if ($order->status === 'paid') {
            return $this->redirectToCheckoutStep($order)
                ->with('status', 'This order has already been paid.');
        }

        if (! $paypal->isConfigured()) {
            return $this->redirectToCheckoutStep($order)
                ->withErrors([
                    'payment' => 'PayPal is not configured for this environment yet.',
                ]);
        }

        return redirect()->away($paypal->startCheckout($order->loadMissing('items')));
    }

    /**
     * Handle the PayPal approval return.
     */
    public function complete(Request $request, PayPalPaymentService $paypal): RedirectResponse
    {
        $token = (string) $request->query('token');
        abort_unless($token !== '', 404);

        $order = $paypal->captureApprovedOrder($token);
        $request->session()->put('checkout.last_order_email', $order->customer_email);
        $request->session()->put('checkout.completed_order_id', $order->id);

        return redirect()
            ->route($order->placed_at ? 'checkout.complete' : 'checkout.index')
            ->with('status', 'PayPal payment processed.');
    }

    /**
     * Handle the PayPal cancel return.
     */
    public function cancel(Request $request, Order $order, PayPalPaymentService $paypal): RedirectResponse
    {
        $this->abortUnlessCanAccessOrder($request, $order);

        $paypal->markCancelled($order);

        return redirect()
            ->route($order->placed_at ? 'checkout.show' : 'checkout.index', $order->placed_at ? $order : [])
            ->with('status', 'PayPal checkout was cancelled.');
    }

    /**
     * Handle PayPal webhooks.
     */
    public function webhook(Request $request, PayPalPaymentService $paypal): Response
    {
        $paypal->handleWebhook($request->headers->all(), $request->all());

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
