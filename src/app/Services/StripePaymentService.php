<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\SavedPaymentMethod;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Stripe\Exception\ApiErrorException;

class StripePaymentService
{
    public function __construct(
        private readonly StripeGateway $gateway,
        private ?OrderCompletionMailService $orderCompletionMailService = null,
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

        $order->loadMissing('user');

        $payload = [
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'client_reference_id' => (string) $order->id,
            'success_url' => route('checkout.payment').'?stripe=success&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.payment').'?stripe=cancelled',
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
        ];

        if (filled($order->user?->stripe_customer_id)) {
            $payload['customer'] = $order->user->stripe_customer_id;
        } else {
            $payload['customer_email'] = $order->customer_email;
        }

        $session = $this->gateway->createCheckoutSession($payload);

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
     * Start a hosted Stripe setup flow for a saved card.
     *
     * @throws ApiErrorException
     */
    public function startPaymentMethodSetup(User $user): string
    {
        if (! $this->isConfigured()) {
            throw new InvalidArgumentException('Stripe is not configured.');
        }

        $customerId = $user->stripe_customer_id;

        if (! $customerId) {
            $customer = $this->gateway->createCustomer([
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => [
                    'user_id' => (string) $user->id,
                ],
            ]);

            $customerId = $customer['id'];
            $user->forceFill(['stripe_customer_id' => $customerId])->save();
        }

        $session = $this->gateway->createCheckoutSession([
            'mode' => 'setup',
            'customer' => $customerId,
            'payment_method_types' => ['card'],
            'success_url' => route('account.payment-methods.index').'?stripe=success&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('account.payment-methods.index').'?stripe=cancelled',
            'metadata' => [
                'user_id' => (string) $user->id,
            ],
        ]);

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

        if ($event->type === 'checkout.session.completed' && $session->mode === 'setup') {
            $this->storeSavedPaymentMethod($session);

            return;
        }

        $status = match ($event->type) {
            'checkout.session.completed' => $session->payment_status === 'paid' ? 'paid' : 'pending',
            'checkout.session.async_payment_failed' => 'failed',
            'checkout.session.expired' => 'cancelled',
        };

        $order = DB::transaction(function () use ($session, $status): ?Order {
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
                        default => 'awaiting_payment',
                    },
                    'placed_at' => $status === 'paid'
                        ? ($payment->order->placed_at ?? now())
                        : $payment->order->placed_at,
                ])->save();

                return $payment->order->fresh(['items.product', 'payments']);
            }
            
            return null;
        });

        if ($status === 'paid' && $order) {
            $this->orderCompletionMailService()->sendFor($order);
        }
    }

    /**
     * Determine whether Stripe is configured.
     */
    public function isConfigured(): bool
    {
        return filled(config('services.stripe.key')) && filled(config('services.stripe.secret'));
    }

    /**
     * Store a saved payment method after Stripe redirects back.
     */
    public function completePaymentMethodSetup(User $user, string $sessionId): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $session = $this->gateway->retrieveCheckoutSession($sessionId);

        if (
            ($session['mode'] ?? null) !== 'setup'
            || (int) ($session['metadata']['user_id'] ?? 0) !== (int) $user->id
            || blank($session['setup_intent'])
        ) {
            return;
        }

        $this->storeSavedPaymentMethod((object) [
            'customer' => $session['customer'],
            'metadata' => (object) $session['metadata'],
            'setup_intent' => $session['setup_intent'],
        ]);
    }

    /**
     * Finalize a Stripe payment checkout after the success redirect.
     */
    public function completeCheckoutPayment(string $sessionId): ?Order
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $session = $this->gateway->retrieveCheckoutSession($sessionId);

        if (
            ($session['mode'] ?? null) !== 'payment'
            || blank($session['metadata']['order_id'] ?? null)
        ) {
            return null;
        }

        $status = ($session['payment_status'] ?? null) === 'paid' ? 'paid' : 'awaiting_payment';
        $orderId = (int) $session['metadata']['order_id'];

        $order = DB::transaction(function () use ($session, $status, $orderId): ?Order {
            $payment = Payment::query()->firstOrNew([
                'provider' => 'stripe',
                'transaction_id' => $session['id'],
            ]);

            if (! $payment->exists) {
                $payment->order_id = $orderId;
                $payment->amount = (int) ($session['amount_total'] ?? 0);
            }

            $payment->status = $status;
            $payment->save();

            $order = $payment->order;

            if (! $order) {
                return null;
            }

            $order->forceFill([
                'status' => match ($status) {
                    'paid' => 'paid',
                    default => 'awaiting_payment',
                },
                'placed_at' => $status === 'paid'
                    ? ($order->placed_at ?? now())
                    : $order->placed_at,
            ])->save();

            return $order->fresh(['items.product', 'payments']);
        });

        if ($status === 'paid' && $order) {
            $this->orderCompletionMailService()->sendFor($order);
        }

        return $order;
    }

    private function orderCompletionMailService(): OrderCompletionMailService
    {
        return $this->orderCompletionMailService ??= app(OrderCompletionMailService::class);
    }

    /**
     * Store a Stripe-hosted saved payment method after setup completion.
     */
    protected function storeSavedPaymentMethod(object $session): void
    {
        $userId = (int) ($session->metadata->user_id ?? 0);
        $setupIntentId = (string) ($session->setup_intent ?? '');

        if ($userId <= 0 || $setupIntentId === '') {
            return;
        }

        $paymentMethod = $this->gateway->retrieveSetupIntent($setupIntentId);

        DB::transaction(function () use ($session, $userId, $paymentMethod): void {
            $user = User::query()->find($userId);

            if (! $user) {
                return;
            }

            if (filled($session->customer) && $user->stripe_customer_id !== (string) $session->customer) {
                $user->forceFill([
                    'stripe_customer_id' => (string) $session->customer,
                ])->save();
            }

            $hasDefault = $user->savedPaymentMethods()->where('is_default', true)->exists();
            $savedPaymentMethod = SavedPaymentMethod::query()->firstOrNew([
                'provider' => 'stripe',
                'provider_payment_method_id' => $paymentMethod['payment_method_id'],
            ]);

            $shouldBeDefault = ! $savedPaymentMethod->exists && ! $hasDefault;

            $savedPaymentMethod->fill([
                'user_id' => $user->id,
                'brand' => $paymentMethod['brand'],
                'last4' => $paymentMethod['last4'],
                'exp_month' => $paymentMethod['exp_month'],
                'exp_year' => $paymentMethod['exp_year'],
                'is_default' => $savedPaymentMethod->exists ? $savedPaymentMethod->is_default : $shouldBeDefault,
            ]);
            $savedPaymentMethod->save();
        });
    }
}
