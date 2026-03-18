<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\PayPalPaymentService;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PayPalCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    public function test_authenticated_user_can_start_a_paypal_checkout_session(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);

        $paypal = Mockery::mock(PayPalPaymentService::class);
        $paypal->shouldReceive('isConfigured')->once()->andReturn(true);
        $paypal->shouldReceive('startCheckout')->once()->andReturn('https://paypal.test/checkout');
        $this->app->instance(PayPalPaymentService::class, $paypal);

        $this->actingAs($user)
            ->post(route('paypal.checkout.store', $order))
            ->assertRedirect('https://paypal.test/checkout');
    }

    public function test_paypal_checkout_redirects_back_when_not_configured(): void
    {
        $order = $this->order();

        $paypal = Mockery::mock(PayPalPaymentService::class);
        $paypal->shouldReceive('isConfigured')->once()->andReturn(false);
        $this->app->instance(PayPalPaymentService::class, $paypal);

        $this->withSession([
            'checkout.last_order_email' => $order->customer_email,
        ])->post(route('paypal.checkout.store', $order))
            ->assertRedirect(route('checkout.show', $order))
            ->assertSessionHasErrors('payment');
    }

    public function test_paypal_return_captures_the_order_and_redirects_to_confirmation(): void
    {
        $order = $this->order();

        $paypal = Mockery::mock(PayPalPaymentService::class);
        $paypal->shouldReceive('captureApprovedOrder')->once()->with('PAYPAL-TOKEN')->andReturn($order);
        $this->app->instance(PayPalPaymentService::class, $paypal);

        $this->get(route('paypal.checkout.return', ['token' => 'PAYPAL-TOKEN']))
            ->assertRedirect(route('checkout.show', $order))
            ->assertSessionHas('status', 'PayPal payment processed.');
    }

    public function test_paypal_webhook_endpoint_calls_the_service(): void
    {
        $paypal = Mockery::mock(PayPalPaymentService::class);
        $paypal->shouldReceive('handleWebhook')->once();
        $this->app->instance(PayPalPaymentService::class, $paypal);

        $this->postJson(route('paypal.webhook'), [
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
        ], [
            'PayPal-Transmission-Id' => 'abc',
        ])->assertOk();
    }

    private function order(?User $user = null): Order
    {
        $order = Order::query()->create([
            'user_id' => $user?->id,
            'status' => 'pending',
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
            'placed_at' => now(),
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => null,
            'product_name' => 'Forge Mark Tee',
            'product_sku' => 'VF-TEE-001',
            'unit_price_cents' => 2800,
            'quantity' => 1,
            'line_total_cents' => 2800,
        ]);

        return $order;
    }
}
