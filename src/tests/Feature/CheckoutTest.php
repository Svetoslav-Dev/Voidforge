<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    public function test_guest_checkout_creates_an_order_and_clears_the_cart(): void
    {
        $product = $this->product(stock: 5, priceCents: 2800);

        $response = $this->withSession([
            'cart.items' => [$product->id.':L' => 2],
        ])->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::query()->with('items')->first();

        $response
            ->assertRedirect(route('checkout.show', $order))
            ->assertSessionHas('status', 'Order placed successfully.');

        $this->assertNotNull($order);
        $this->assertSame('pending', $order->status);
        $this->assertSame('EUR', $order->currency);
        $this->assertSame(5600, $order->total_cents);
        $this->assertSame('Taylor Forge', $order->customer_name);
        $this->assertSame([], session('cart.items', []));
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertCount(1, $order->items);
        $this->assertSame('Forge Mark Tee', $order->items->first()->product_name);
        $this->assertSame('L', $order->items->first()->product_size);
    }

    public function test_authenticated_checkout_links_the_order_to_the_user(): void
    {
        $user = User::factory()->create();
        $product = $this->product();

        $this->actingAs($user)
            ->withSession([
                'cart.items' => [$product->id.':M' => 1],
            ])->post(route('checkout.store'), $this->checkoutPayload([
                'customer_name' => $user->name,
                'customer_email' => $user->email,
            ]))->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'customer_email' => $user->email,
        ]);
    }

    public function test_checkout_redirects_back_to_cart_when_the_cart_is_empty(): void
    {
        $this->get(route('checkout.index'))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('status', 'Your cart is empty.');
    }

    public function test_checkout_fails_when_stock_is_no_longer_available(): void
    {
        $product = $this->product(stock: 1);

        $response = $this->withSession([
            'cart.items' => [$product->id.':S' => 2],
        ])->from(route('checkout.index'))
            ->post(route('checkout.store'), $this->checkoutPayload());

        $response
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame([$product->id.':S' => 2], session('cart.items'));
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

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function checkoutPayload(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Taylor Forge',
            'customer_email' => 'taylor@example.test',
            'customer_phone' => '+1-555-0100',
            'shipping_address_line_1' => '123 Ember Street',
            'shipping_address_line_2' => 'Unit B',
            'shipping_city' => 'Ironvale',
            'shipping_state' => 'CA',
            'shipping_postal_code' => '90210',
            'shipping_country' => 'bg',
        ], $overrides);
    }
}
