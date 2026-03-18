<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use PaypalServerSdkLib\Models\Builders\AmountBreakdownBuilder;
use PaypalServerSdkLib\Models\Builders\AmountWithBreakdownBuilder;
use PaypalServerSdkLib\Models\Builders\AddressBuilder;
use PaypalServerSdkLib\Models\Builders\ItemRequestBuilder;
use PaypalServerSdkLib\Models\Builders\MoneyBuilder;
use PaypalServerSdkLib\Models\Builders\OrderApplicationContextBuilder;
use PaypalServerSdkLib\Models\Builders\OrderRequestBuilder;
use PaypalServerSdkLib\Models\Builders\PurchaseUnitRequestBuilder;
use PaypalServerSdkLib\Models\Builders\ShippingDetailsBuilder;
use PaypalServerSdkLib\Models\Builders\ShippingNameBuilder;

class PayPalPaymentService
{
    public function __construct(
        private readonly PayPalGateway $gateway
    ) {
    }

    /**
     * Start a PayPal approval flow for an order.
     */
    public function startCheckout(Order $order): string
    {
        if (! $this->isConfigured()) {
            throw new InvalidArgumentException('PayPal is not configured.');
        }

        $client = $this->gateway->client();
        $response = $client->getOrdersController()->createOrder([
            'body' => OrderRequestBuilder::init(
                'CAPTURE',
                [$this->purchaseUnit($order)]
            )->applicationContext(
                OrderApplicationContextBuilder::init()
                    ->brandName(config('app.name'))
                    ->userAction('PAY_NOW')
                    ->returnUrl(route('paypal.checkout.return'))
                    ->cancelUrl(route('paypal.checkout.cancel', ['order' => $order]))
                    ->build()
            )->build(),
            'prefer' => 'return=representation',
        ]);

        $paypalOrder = $response->getResult();
        $paypalOrderId = (string) $paypalOrder->getId();

        Payment::query()->updateOrCreate(
            [
                'provider' => 'paypal',
                'transaction_id' => $paypalOrderId,
            ],
            [
                'order_id' => $order->id,
                'amount' => $order->total_cents,
                'status' => 'pending',
            ]
        );

        return $this->approvalUrl($paypalOrder->getLinks() ?? []);
    }

    /**
     * Capture an approved PayPal order.
     */
    public function captureApprovedOrder(string $paypalOrderId): Order
    {
        $payment = Payment::query()
            ->where('provider', 'paypal')
            ->where('transaction_id', $paypalOrderId)
            ->firstOrFail();

        $response = $this->gateway->client()->getOrdersController()->captureOrder([
            'id' => $paypalOrderId,
            'prefer' => 'return=representation',
        ]);

        $paypalOrder = $response->getResult();
        $capture = Arr::first(
            Arr::first($paypalOrder->getPurchaseUnits() ?? [])?->getPayments()?->getCaptures() ?? []
        );

        $payment->forceFill([
            'status' => $this->mapCaptureStatus($capture?->getStatus()),
            'amount' => $payment->amount ?: $payment->order->total_cents,
        ])->save();

        $payment->order->forceFill([
            'status' => $payment->status === 'paid' ? 'paid' : 'payment_failed',
        ])->save();

        return $payment->order->fresh('items');
    }

    /**
     * Mark a PayPal order as cancelled.
     */
    public function markCancelled(Order $order): void
    {
        $payment = $order->payments()
            ->where('provider', 'paypal')
            ->latest('id')
            ->first();

        if (! $payment || $payment->status === 'paid') {
            return;
        }

        $payment->forceFill([
            'status' => 'cancelled',
        ])->save();

        $order->forceFill([
            'status' => 'cancelled',
        ])->save();
    }

    /**
     * Process a verified PayPal webhook payload.
     *
     * @param  array<string, mixed>  $headers
     * @param  array<string, mixed>  $body
     */
    public function handleWebhook(array $headers, array $body): void
    {
        if (! $this->isConfigured() || blank(config('services.paypal.webhook_id'))) {
            return;
        }

        $verification = $this->gateway->verifyWebhookSignature([
            'auth_algo' => Arr::first($headers['paypal-auth-algo'] ?? []),
            'cert_url' => Arr::first($headers['paypal-cert-url'] ?? []),
            'transmission_id' => Arr::first($headers['paypal-transmission-id'] ?? []),
            'transmission_sig' => Arr::first($headers['paypal-transmission-sig'] ?? []),
            'transmission_time' => Arr::first($headers['paypal-transmission-time'] ?? []),
            'webhook_id' => (string) config('services.paypal.webhook_id'),
            'webhook_event' => $body,
        ]);

        if ($verification->json('verification_status') !== 'SUCCESS') {
            return;
        }

        $eventType = (string) Arr::get($body, 'event_type');

        if (! in_array($eventType, [
            'PAYMENT.CAPTURE.COMPLETED',
            'PAYMENT.CAPTURE.PENDING',
            'PAYMENT.CAPTURE.DENIED',
        ], true)) {
            return;
        }

        $orderId = (int) (
            Arr::get($body, 'resource.custom_id')
            ?? Arr::get($body, 'resource.purchase_units.0.custom_id')
            ?? 0
        );

        if ($orderId === 0) {
            return;
        }

        DB::transaction(function () use ($body, $eventType, $orderId): void {
            $payment = Payment::query()
                ->where('provider', 'paypal')
                ->where('order_id', $orderId)
                ->latest('id')
                ->first();

            if (! $payment) {
                $payment = new Payment([
                    'provider' => 'paypal',
                    'transaction_id' => (string) (Arr::get($body, 'resource.supplementary_data.related_ids.order_id')
                        ?? Arr::get($body, 'resource.id')
                        ?? 'paypal-'.$orderId),
                    'order_id' => $orderId,
                    'amount' => (int) round(((float) Arr::get($body, 'resource.amount.value', 0)) * 100),
                ]);
            }

            $payment->status = match ($eventType) {
                'PAYMENT.CAPTURE.COMPLETED' => 'paid',
                'PAYMENT.CAPTURE.PENDING' => 'pending',
                'PAYMENT.CAPTURE.DENIED' => 'failed',
            };
            $payment->save();

            $payment->order?->forceFill([
                'status' => match ($payment->status) {
                    'paid' => 'paid',
                    'failed' => 'payment_failed',
                    default => 'pending',
                },
            ])->save();
        });
    }

    /**
     * Determine whether PayPal is configured.
     */
    public function isConfigured(): bool
    {
        return filled(config('services.paypal.client_id'))
            && filled(config('services.paypal.client_secret'));
    }

    /**
     * Build the purchase unit for the order.
     */
    private function purchaseUnit(Order $order): \PaypalServerSdkLib\Models\PurchaseUnitRequest
    {
        $currency = $order->currency;
        $amount = $this->formatMoney($order->total_cents);
        $itemTotal = $this->formatMoney($order->subtotal_cents);

        return PurchaseUnitRequestBuilder::init(
            AmountWithBreakdownBuilder::init($currency, $amount)
                ->breakdown(
                    AmountBreakdownBuilder::init()
                        ->itemTotal(MoneyBuilder::init($currency, $itemTotal)->build())
                        ->shipping(MoneyBuilder::init($currency, $this->formatMoney($order->shipping_cents))->build())
                        ->build()
                )
                ->build()
        )
            ->customId((string) $order->id)
            ->invoiceId('VOIDFORGE-'.$order->id)
            ->description('Voidforge order #'.$order->id)
            ->items($order->items->map(fn ($item) => ItemRequestBuilder::init(
                $item->product_name.' ('.$item->product_size.')',
                MoneyBuilder::init($currency, $this->formatMoney($item->unit_price_cents))->build(),
                (string) $item->quantity
            )->sku($item->product_sku)->build())->all())
            ->shipping(
                ShippingDetailsBuilder::init()
                    ->name(
                        ShippingNameBuilder::init()
                            ->fullName($order->customer_name)
                            ->build()
                    )
                    ->emailAddress($order->customer_email)
                    ->address(
                        AddressBuilder::init($order->shipping_country)
                            ->addressLine1($order->shipping_address_line_1)
                            ->addressLine2($order->shipping_address_line_2)
                            ->adminArea2($order->shipping_city)
                            ->adminArea1($order->shipping_state)
                            ->postalCode($order->shipping_postal_code)
                            ->build()
                    )
                    ->build()
            )
            ->build();
    }

    /**
     * Extract the approval URL from the PayPal response.
     *
     * @param  array<int, mixed>  $links
     */
    private function approvalUrl(array $links): string
    {
        foreach ($links as $link) {
            if ($link->getRel() === 'approve') {
                return $link->getHref();
            }
        }

        throw new InvalidArgumentException('PayPal approval link was not returned.');
    }

    /**
     * Convert integer cents to a PayPal money string.
     */
    private function formatMoney(int $amountCents): string
    {
        return number_format($amountCents / 100, 2, '.', '');
    }

    /**
     * Map PayPal capture status to the local payment status.
     */
    private function mapCaptureStatus(?string $status): string
    {
        return match ($status) {
            'COMPLETED' => 'paid',
            'PENDING' => 'pending',
            default => 'failed',
        };
    }
}
