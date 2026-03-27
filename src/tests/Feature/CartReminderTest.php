<?php

namespace Tests\Feature;

use App\Jobs\SendCartReminderEmail;
use App\Models\CartReminder;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CartReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    public function test_authenticated_cart_changes_sync_a_server_side_cart_snapshot(): void
    {
        $user = User::factory()->create();
        $product = $this->product();

        $this->actingAs($user)->post(route('cart.store', $product), [
            'quantity' => 2,
            'size' => 'M',
        ])->assertRedirect();

        $reminder = CartReminder::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($reminder);
        $this->assertSame(5600, $reminder->subtotal_cents);
        $this->assertCount(1, $reminder->items);
        $this->assertSame('Forge Mark Tee', $reminder->items[0]['product_name']);
        $this->assertSame('M', $reminder->items[0]['size']);
        $this->assertSame(2, $reminder->items[0]['quantity']);
    }

    public function test_reminder_command_only_queues_carts_idle_for_two_days(): void
    {
        Queue::fake();

        $oldUser = User::factory()->create(['email' => 'old@example.test']);
        $freshUser = User::factory()->create(['email' => 'fresh@example.test']);

        $oldReminder = CartReminder::query()->create([
            'user_id' => $oldUser->id,
            'items' => [[
                'product_id' => 1,
                'product_name' => 'Forge Mark Tee',
                'product_slug' => 'forge-mark-tee',
                'product_image_url' => null,
                'size' => 'M',
                'quantity' => 1,
                'line_total_cents' => 2800,
            ]],
            'subtotal_cents' => 2800,
            'discount_code' => null,
            'last_activity_at' => now()->subDays(3),
            'reminder_sent_at' => null,
        ]);

        CartReminder::query()->create([
            'user_id' => $freshUser->id,
            'items' => [[
                'product_id' => 1,
                'product_name' => 'Forge Mark Tee',
                'product_slug' => 'forge-mark-tee',
                'product_image_url' => null,
                'size' => 'M',
                'quantity' => 1,
                'line_total_cents' => 2800,
            ]],
            'subtotal_cents' => 2800,
            'discount_code' => null,
            'last_activity_at' => now()->subDay(),
            'reminder_sent_at' => null,
        ]);

        $this->artisan('cart:send-reminders')
            ->expectsOutput('Queued 1 cart reminder email.')
            ->assertExitCode(0);

        Queue::assertPushed(SendCartReminderEmail::class, function (SendCartReminderEmail $job) use ($oldReminder): bool {
            return $job->cartReminderId === $oldReminder->id;
        });
        Queue::assertPushed(SendCartReminderEmail::class, 1);

        $this->assertNotNull($oldReminder->fresh()->reminder_sent_at);
    }

    public function test_paid_checkout_completion_clears_the_authenticated_cart_reminder(): void
    {
        $user = User::factory()->create([
            'email' => 'taylor@example.test',
        ]);
        $product = $this->product(stock: 5, priceCents: 2800);

        $this->actingAs($user)->post(route('cart.store', $product), [
            'quantity' => 1,
            'size' => 'L',
        ]);

        $order = \App\Models\Order::query()->create([
            'user_id' => $user->id,
            'status' => 'paid',
            'currency' => 'EUR',
            'subtotal_cents' => 2800,
            'shipping_cents' => 0,
            'total_cents' => 2800,
            'customer_name' => 'Taylor Forge',
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

        $this->actingAs($user)
            ->withSession([
                'cart.items' => [$product->id.':L' => 1],
                'checkout.pending_order_id' => $order->id,
                'checkout.completed_order_id' => $order->id,
            ])->get(route('checkout.complete'))
            ->assertOk();

        $this->assertDatabaseMissing('cart_reminders', [
            'user_id' => $user->id,
        ]);
    }

    private function product(int $stock = 25, int $priceCents = 2800): Product
    {
        $category = Category::factory()->create([
            'name' => 'Classic Tees',
            'slug' => 'classic-tees',
        ]);

        return Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Forge Mark Tee',
            'slug' => 'forge-mark-tee',
            'sku' => 'VF-TEE-001',
            'price_cents' => $priceCents,
            'stock' => $stock,
            'is_active' => true,
        ]);
    }
}
