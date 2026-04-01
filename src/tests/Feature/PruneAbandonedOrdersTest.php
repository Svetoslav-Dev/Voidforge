<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneAbandonedOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_prune_command_soft_deletes_old_awaiting_payment_orders(): void
    {
        $user = User::factory()->create();

        $base = [
            'user_id' => $user->id,
            'currency' => 'EUR',
            'subtotal_cents' => 2800,
            'shipping_cents' => 0,
            'total_cents' => 2800,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'shipping_address_line_1' => '1 Test St',
            'shipping_city' => 'Sofia',
            'shipping_postal_code' => '1000',
            'shipping_country' => 'BG',
        ];

        $old = Order::query()->create(array_merge($base, ['status' => 'awaiting_payment']));
        $old->timestamps = false;
        $old->created_at = now()->subDays(31);
        $old->save();

        $recent = Order::query()->create(array_merge($base, ['status' => 'awaiting_payment']));

        $paid = Order::query()->create(array_merge($base, [
            'status' => 'paid',
            'placed_at' => now()->subDays(31),
            'created_at' => now()->subDays(31),
        ]));

        $this->artisan('orders:prune-abandoned')->assertSuccessful();

        $this->assertSoftDeleted($old);
        $this->assertNotSoftDeleted($recent);
        $this->assertNotSoftDeleted($paid);
    }
}
