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
            ->assertSee((string) $order->id);
    }

    public function test_user_can_open_their_own_receipt_but_not_another_users_receipt(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownOrder = $this->orderFor($user, transactionId: 'tx-own');
        $otherOrder = $this->orderFor($otherUser, transactionId: 'tx-other');

        $this->actingAs($user)
            ->get(route('orders.show', $ownOrder))
            ->assertOk()
            ->assertSee('Transaction: tx-own');

        $this->actingAs($user)
            ->get(route('orders.show', $otherOrder))
            ->assertNotFound();
    }

    private function orderFor(User $user, string $transactionId = 'tx-1000'): Order
    {
        $order = Order::query()->create([
            'user_id' => $user->id,
            'status' => 'paid',
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

        Payment::query()->create([
            'order_id' => $order->id,
            'provider' => 'stripe',
            'transaction_id' => $transactionId,
            'amount' => 2800,
            'status' => 'paid',
        ]);

        return $order;
    }
}
