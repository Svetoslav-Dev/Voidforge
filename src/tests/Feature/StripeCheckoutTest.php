<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\StripePaymentService;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class StripeCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    public function test_authenticated_user_can_start_a_stripe_checkout_session(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);

        $stripe = Mockery::mock(StripePaymentService::class);
        $stripe->shouldReceive('isConfigured')->once()->andReturn(true);
        $stripe->shouldReceive('startCheckout')->once()->andReturn('https://checkout.stripe.test/session/123');
        $this->app->instance(StripePaymentService::class, $stripe);

        $this->actingAs($user)
            ->post(route('stripe.checkout.store', $order))
            ->assertRedirect('https://checkout.stripe.test/session/123');
    }

    public function test_guest_can_start_a_stripe_checkout_session_for_their_last_order(): void
    {
        $order = $this->order();

        $stripe = Mockery::mock(StripePaymentService::class);
        $stripe->shouldReceive('isConfigured')->once()->andReturn(true);
        $stripe->shouldReceive('startCheckout')->once()->andReturn('https://checkout.stripe.test/session/guest');
        $this->app->instance(StripePaymentService::class, $stripe);

        $this->withSession([
            'checkout.last_order_email' => $order->customer_email,
        ])->post(route('stripe.checkout.store', $order))
            ->assertRedirect('https://checkout.stripe.test/session/guest');
    }

    public function test_stripe_checkout_redirects_back_when_not_configured(): void
    {
        $order = $this->order();

        $stripe = Mockery::mock(StripePaymentService::class);
        $stripe->shouldReceive('isConfigured')->once()->andReturn(false);
        $this->app->instance(StripePaymentService::class, $stripe);

        $this->withSession([
            'checkout.last_order_email' => $order->customer_email,
        ])->post(route('stripe.checkout.store', $order))
            ->assertRedirect(route('checkout.complete'))
            ->assertSessionHasErrors('payment');
    }

    public function test_stripe_webhook_endpoint_calls_the_service(): void
    {
        $stripe = Mockery::mock(StripePaymentService::class);
        $stripe->shouldReceive('handleWebhook')->once()->with('{"id":"evt_123"}', 't=1,v1=test');
        $this->app->instance(StripePaymentService::class, $stripe);

        $this->call(
            'POST',
            route('stripe.webhook'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => 't=1,v1=test',
            ],
            '{"id":"evt_123"}'
        )->assertOk();
    }

    public function test_successful_stripe_return_finalizes_the_order_and_redirects_to_confirmation(): void
    {
        $order = $this->order();

        $stripe = Mockery::mock(StripePaymentService::class);
        $stripe->shouldReceive('completeCheckoutPayment')->once()->with('cs_test_123')->andReturn($order->forceFill([
            'status' => 'paid',
            'placed_at' => now(),
        ]));
        $this->app->instance(StripePaymentService::class, $stripe);

        $this->withSession([
            'checkout.last_order_email' => $order->customer_email,
            'checkout.pending_order_id' => $order->id,
        ])->get(route('checkout.index', [
            'stripe' => 'success',
            'session_id' => 'cs_test_123',
        ]))
            ->assertRedirect(route('checkout.complete'))
            ->assertSessionHas('status', 'Stripe payment processed.');
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
