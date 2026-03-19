<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartService $cart;

    private CheckoutService $checkout;

    protected function setUp(): void
    {
        parent::setUp();

        session()->start();
        $this->cart = app(CartService::class);
        $this->checkout = app(CheckoutService::class);
    }

    public function test_it_creates_an_awaiting_payment_order_snapshots_items_and_keeps_the_cart(): void
    {
        $user = User::factory()->create();
        $product = $this->product(stock: 8, priceCents: 3400);
        $this->cart->add($product, 'XL', 2);

        $order = $this->checkout->placeOrder($this->customerData(), $user);

        $this->assertSame('awaiting_payment', $order->status);
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame(6800, $order->total_cents);
        $this->assertCount(1, $order->items);
        $this->assertSame('Forge Mark Tee', $order->items->first()->product_name);
        $this->assertSame('VF-TEE-001', $order->items->first()->product_sku);
        $this->assertSame('XL', $order->items->first()->product_size);
        $this->assertSame([$product->id.':XL' => 2], session('cart.items', []));
        $this->assertSame('taylor@example.test', session('checkout.last_order_email'));
        $this->assertSame(6, $product->fresh()->stock);
    }

    public function test_it_rejects_checkout_when_stock_changed_after_item_was_added_to_the_cart(): void
    {
        $product = $this->product(stock: 5);
        $this->cart->add($product, 'M', 3);

        $product->update(['stock' => 2]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('One of the selected products no longer has enough stock.');

        $this->checkout->placeOrder($this->customerData());
    }

    private function customerData(): array
    {
        return [
            'customer_name' => 'Taylor Forge',
            'customer_email' => 'taylor@example.test',
            'customer_phone' => '+1-555-0100',
            'shipping_address_line_1' => '123 Ember Street',
            'shipping_address_line_2' => 'Unit B',
            'shipping_city' => 'Ironvale',
            'shipping_state' => 'CA',
            'shipping_postal_code' => '90210',
            'shipping_country' => 'US',
        ];
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
