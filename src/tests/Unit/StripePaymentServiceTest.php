<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\OrderCompletionMailService;
use App\Services\StripeGateway;
use App\Services\StripePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class StripePaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uses_the_saved_stripe_customer_for_checkout_when_available(): void
    {
        config()->set('services.stripe.key', 'pk_test_example');
        config()->set('services.stripe.secret', 'sk_test_example');

        $user = User::factory()->create([
            'stripe_customer_id' => 'cus_saved_123',
        ]);
        $order = $this->order($user);

        $gateway = Mockery::mock(StripeGateway::class);
        $gateway->shouldReceive('createCheckoutSession')
            ->once()
            ->withArgs(function (array $payload): bool {
                return ($payload['customer'] ?? null) === 'cus_saved_123'
                    && ! array_key_exists('customer_email', $payload)
                    && ($payload['payment_method_types'] ?? null) === ['card'];
            })
            ->andReturn([
                'id' => 'cs_test_saved',
                'url' => 'https://checkout.stripe.test/saved',
            ]);

        $service = new StripePaymentService($gateway);

        $url = $service->startCheckout($order);

        $this->assertSame('https://checkout.stripe.test/saved', $url);
    }

    public function test_it_falls_back_to_customer_email_when_no_saved_stripe_customer_exists(): void
    {
        config()->set('services.stripe.key', 'pk_test_example');
        config()->set('services.stripe.secret', 'sk_test_example');

        $user = User::factory()->create([
            'stripe_customer_id' => null,
            'email' => 'cookie@example.test',
        ]);
        $order = $this->order($user);

        $gateway = Mockery::mock(StripeGateway::class);
        $gateway->shouldReceive('createCheckoutSession')
            ->once()
            ->withArgs(function (array $payload): bool {
                return ($payload['customer_email'] ?? null) === 'cookie@example.test'
                    && ! array_key_exists('customer', $payload)
                    && ($payload['payment_method_types'] ?? null) === ['card'];
            })
            ->andReturn([
                'id' => 'cs_test_email',
                'url' => 'https://checkout.stripe.test/email',
            ]);

        $service = new StripePaymentService($gateway);

        $url = $service->startCheckout($order);

        $this->assertSame('https://checkout.stripe.test/email', $url);
    }

    public function test_it_sends_the_completion_email_when_a_checkout_session_is_paid(): void
    {
        config()->set('services.stripe.key', 'pk_test_example');
        config()->set('services.stripe.secret', 'sk_test_example');

        $order = $this->order();

        $gateway = Mockery::mock(StripeGateway::class);
        $gateway->shouldReceive('retrieveCheckoutSession')
            ->once()
            ->with('cs_test_paid')
            ->andReturn([
                'id' => 'cs_test_paid',
                'mode' => 'payment',
                'payment_status' => 'paid',
                'amount_total' => 2800,
                'metadata' => [
                    'order_id' => (string) $order->id,
                ],
            ]);

        $mailer = Mockery::mock(OrderCompletionMailService::class);
        $mailer->shouldReceive('sendFor')
            ->once()
            ->withArgs(fn (Order $paidOrder): bool => $paidOrder->is($order) && $paidOrder->status === 'paid');

        $service = new StripePaymentService($gateway, $mailer);

        $paidOrder = $service->completeCheckoutPayment('cs_test_paid');

        $this->assertNotNull($paidOrder);
        $this->assertSame('paid', $paidOrder->status);
        $this->assertNotNull($paidOrder->placed_at);
    }

    private function order(?User $user = null): Order
    {
        $order = Order::query()->create([
            'user_id' => $user?->id,
            'status' => 'awaiting_payment',
            'currency' => 'EUR',
            'subtotal_cents' => 2800,
            'shipping_cents' => 0,
            'total_cents' => 2800,
            'customer_name' => $user?->name ?? 'Taylor Forge',
            'customer_email' => $user?->email ?? 'taylor@example.test',
            'customer_phone' => '+1-555-0100',
            'shipping_address_line_1' => '123 Ember Street',
            'shipping_address_line_2' => 'Unit B',
            'shipping_city' => 'Ironvale',
            'shipping_state' => 'CA',
            'shipping_postal_code' => '90210',
            'shipping_country' => 'US',
            'placed_at' => null,
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => null,
            'product_name' => 'Forge Mark Tee',
            'product_sku' => 'VF-TEE-001',
            'product_size' => 'M',
            'unit_price_cents' => 2800,
            'quantity' => 1,
            'line_total_cents' => 2800,
        ]);

        return $order;
    }
}
