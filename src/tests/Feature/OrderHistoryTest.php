<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_purchase_history(): void
    {
        $user = User::factory()->create();
        $order = $this->orderFor($user);

        $this->actingAs($user)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee('Purchase History')
            ->assertSee('Forge Mark Tee')
            ->assertSee('VF'.$order->id);

        $this->assertSame(
            '/orders/VF'.$order->id,
            route('orders.show', ['orderReference' => 'VF'.$order->id], false)
        );
    }

    public function test_user_can_open_their_own_receipt_but_not_another_users_receipt(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $ownOrder = $this->orderFor($user, transactionId: 'tx-own');
        $otherOrder = $this->orderFor($otherUser, transactionId: 'tx-other');

        $this->actingAs($user)
            ->get(route('orders.show', ['orderReference' => 'VF'.$ownOrder->id]))
            ->assertOk()
            ->assertSee('Order #VF'.$ownOrder->id)
            ->assertDontSee('Transaction: tx-own');

        $this->actingAs($user)
            ->get(route('orders.show', ['orderReference' => 'VF'.$otherOrder->id]))
            ->assertNotFound();

        $this->actingAs($admin)
            ->get(route('orders.show', ['orderReference' => 'VF'.$otherOrder->id]))
            ->assertOk()
            ->assertSee('Order #VF'.$otherOrder->id);
    }

    public function test_user_can_download_their_receipt_as_pdf(): void
    {
        $user = User::factory()->create([
            'name' => 'Cookie Destroyer',
            'email' => 'cookie-destroyer@example.test',
        ]);
        $order = $this->orderFor($user, transactionId: 'tx-pdf');

        $this->actingAs($user)
            ->get(route('orders.download', ['orderReference' => 'VF'.$order->id]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename="voidforge-receipt-'.$order->id.'.pdf"');
    }

    public function test_user_can_open_and_repay_their_pending_order(): void
    {
        $user = User::factory()->create();
        $order = $this->orderFor($user, transactionId: 'tx-pending', status: 'awaiting_payment', placedAt: null);

        $this->actingAs($user)
            ->get(route('orders.show', ['orderReference' => 'VF'.$order->id]))
            ->assertOk()
            ->assertSee('Order #VF'.$order->id)
            ->assertSee('No completed payment has been recorded for this order yet.')
            ->assertSee('Pay with Stripe')
            ->assertSee('Pay with PayPal');
    }

    private function orderFor(User $user, string $transactionId = 'tx-1000', string $status = 'paid', $placedAt = null): Order
    {
        $order = Order::query()->create([
            'user_id' => $user->id,
            'status' => $status,
            'currency' => 'EUR',
            'subtotal_cents' => 2800,
            'shipping_cents' => 0,
            'total_cents' => 2800,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => '+1-555-0100',
            'shipping_address_line_1' => '123 Ember Street',
            'shipping_address_line_2' => 'Unit B',
            'shipping_city' => 'Ironvale',
            'shipping_state' => 'CA',
            'shipping_postal_code' => '90210',
            'shipping_country' => 'US',
            'placed_at' => $placedAt ?? ($status === 'paid' ? now() : null),
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

        Payment::query()->create([
            'order_id' => $order->id,
            'provider' => 'stripe',
            'transaction_id' => $transactionId,
            'amount' => 2800,
            'status' => $status === 'paid' ? 'paid' : 'pending',
        ]);

        return $order;
    }
}
