<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Stripe\Exception\ApiErrorException;

class StripePaymentService
{
    public function __construct(
        private readonly StripeGateway $gateway
    ) {
    }

    /**
     * Start a hosted Stripe Checkout flow for an order.
     *
     * @throws ApiErrorException
     */
    public function startCheckout(Order $order): string
    {
        if (! $this->isConfigured()) {
            throw new InvalidArgumentException('Stripe is not configured.');
        }

        $session = $this->gateway->createCheckoutSession([
            'mode' => 'payment',
            'customer_email' => $order->customer_email,
            'client_reference_id' => (string) $order->id,
            'success_url' => route('checkout.show', $order).'?stripe=success',
            'cancel_url' => route('checkout.show', $order).'?stripe=cancelled',
            'metadata' => [
                'order_id' => (string) $order->id,
            ],
            'line_items' => $order->items->map(fn ($item) => [
                'quantity' => $item->quantity,
                'price_data' => [
                    'currency' => strtolower($order->currency),
                    'unit_amount' => $item->unit_price_cents,
                    'product_data' => [
                        'name' => $item->product_name,
                        'description' => 'Size '.$item->product_size,
                        'metadata' => [
                            'sku' => $item->product_sku,
                            'size' => $item->product_size,
                        ],
                    ],
                ],
            ])->all(),
        ]);

        Payment::query()->updateOrCreate(
            [
                'provider' => 'stripe',
                'transaction_id' => $session['id'],
            ],
            [
                'order_id' => $order->id,
                'amount' => $order->total_cents,
                'status' => 'pending',
            ]
        );

        return $session['url'];
    }

    /**
     * Process a Stripe webhook payload.
     */
    public function handleWebhook(string $payload, ?string $signature): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $event = $this->gateway->constructWebhookEvent($payload, $signature);

        if (! in_array($event->type, [
            'checkout.session.completed',
            'checkout.session.async_payment_failed',
            'checkout.session.expired',
        ], true)) {
            return;
        }

        /** @var \Stripe\Checkout\Session $session */
        $session = $event->data->object;
        $status = match ($event->type) {
            'checkout.session.completed' => $session->payment_status === 'paid' ? 'paid' : 'pending',
            'checkout.session.async_payment_failed' => 'failed',
            'checkout.session.expired' => 'cancelled',
        };

        DB::transaction(function () use ($session, $status): void {
            $payment = Payment::query()->firstOrNew([
                'provider' => 'stripe',
                'transaction_id' => $session->id,
            ]);

            if (! $payment->exists) {
                $orderId = (int) ($session->metadata->order_id ?? $session->client_reference_id ?? 0);
                $payment->order_id = $orderId;
                $payment->amount = (int) ($session->amount_total ?? 0);
            }

            $payment->status = $status;
            $payment->save();

            if ($payment->order) {
                $payment->order->forceFill([
                    'status' => match ($status) {
                        'paid' => 'paid',
                        'failed' => 'payment_failed',
                        'cancelled' => 'cancelled',
                        default => 'pending',
                    },
                ])->save();
            }
        });
    }

    /**
     * Determine whether Stripe is configured.
     */
    public function isConfigured(): bool
    {
        return filled(config('services.stripe.key')) && filled(config('services.stripe.secret'));
    }
}
