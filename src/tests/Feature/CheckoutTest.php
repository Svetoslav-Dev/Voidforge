<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingAddress;
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

    public function test_guest_checkout_creates_an_awaiting_payment_order_and_keeps_the_cart(): void
    {
        $product = $this->product(stock: 5, priceCents: 2800);

        $response = $this->withSession([
            'cart.items' => [$product->id.':L' => 2],
        ])->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::query()->with('items')->first();

        $response->assertRedirect(route('checkout.payment'));

        $this->assertNotNull($order);
        $this->assertSame('awaiting_payment', $order->status);
        $this->assertNull($order->placed_at);
        $this->assertSame('EUR', $order->currency);
        $this->assertSame(5600, $order->total_cents);
        $this->assertSame('Taylor Forge', $order->customer_name);
        $this->assertSame([$product->id.':L' => 2], session('cart.items', []));
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertCount(1, $order->items);
        $this->assertSame('Forge Mark Tee', $order->items->first()->product_name);
        $this->assertSame('L', $order->items->first()->product_size);
    }

    public function test_checkout_applies_the_current_discount_code_to_the_order(): void
    {
        $product = $this->product(stock: 5, priceCents: 2800);
        $discountCode = DiscountCode::query()->create([
            'code' => 'WELCOME10',
            'type' => 'percent',
            'amount' => 10,
            'is_active' => true,
        ]);

        $this->withSession([
            'cart.items' => [$product->id.':L' => 2],
            'cart.discount_code' => $discountCode->code,
        ])->post(route('checkout.store'), $this->checkoutPayload())
            ->assertRedirect(route('checkout.payment'));

        $order = Order::query()->firstOrFail();

        $this->assertSame($discountCode->id, $order->discount_code_id);
        $this->assertSame('WELCOME10', $order->discount_code);
        $this->assertSame(560, $order->discount_cents);
        $this->assertSame(5040, $order->total_cents);
        $this->assertSame(1, $discountCode->fresh()->times_used);
    }

    public function test_paid_checkout_show_clears_the_cart_for_the_same_session(): void
    {
        $product = $this->product(stock: 5, priceCents: 2800);
        $order = Order::query()->create([
            'status' => 'paid',
            'currency' => 'EUR',
            'subtotal_cents' => 2800,
            'shipping_cents' => 0,
            'total_cents' => 2800,
            'customer_name' => 'Taylor Forge',
            'customer_email' => 'taylor@example.test',
            'customer_phone' => '+1-555-0100',
            'shipping_address_line_1' => '123 Ember Street',
            'shipping_address_line_2' => 'Unit B',
            'shipping_city' => 'Ironvale',
            'shipping_state' => 'CA',
            'shipping_postal_code' => '90210',
            'shipping_country' => 'US',
            'placed_at' => now(),
        ]);

        $this->withSession([
            'cart.items' => [$product->id.':L' => 2],
            'checkout.last_order_email' => $order->customer_email,
            'checkout.pending_order_id' => $order->id,
            'checkout.completed_order_id' => $order->id,
        ])->get(route('checkout.complete'))
            ->assertOk();

        $this->assertSame([], session('cart.items', []));
    }

    public function test_paid_checkout_complete_can_download_invoice_pdf(): void
    {
        $order = Order::query()->create([
            'status' => 'paid',
            'currency' => 'EUR',
            'subtotal_cents' => 2800,
            'shipping_cents' => 0,
            'total_cents' => 2800,
            'customer_name' => 'Taylor Forge',
            'customer_email' => 'taylor@example.test',
            'customer_phone' => '+1-555-0100',
            'shipping_address_line_1' => '123 Ember Street',
            'shipping_address_line_2' => 'Unit B',
            'shipping_city' => 'Ironvale',
            'shipping_state' => 'CA',
            'shipping_postal_code' => '90210',
            'shipping_country' => 'US',
            'placed_at' => now(),
        ]);

        $response = $this->withSession([
            'checkout.last_order_email' => $order->customer_email,
            'checkout.completed_order_id' => $order->id,
        ])->get(route('checkout.download'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertHeader('content-disposition', 'attachment; filename="voidforge-receipt-VF'.$order->id.'.pdf"');
    }

    public function test_unpaid_checkout_show_redirects_back_to_clean_checkout_url(): void
    {
        $order = Order::query()->create([
            'status' => 'awaiting_payment',
            'currency' => 'EUR',
            'subtotal_cents' => 2800,
            'shipping_cents' => 0,
            'total_cents' => 2800,
            'customer_name' => 'Taylor Forge',
            'customer_email' => 'taylor@example.test',
            'customer_phone' => '+1-555-0100',
            'shipping_address_line_1' => '123 Ember Street',
            'shipping_address_line_2' => 'Unit B',
            'shipping_city' => 'Ironvale',
            'shipping_state' => 'CA',
            'shipping_postal_code' => '90210',
            'shipping_country' => 'US',
            'placed_at' => null,
        ]);

        $this->withSession([
            'checkout.last_order_email' => $order->customer_email,
        ])->get(route('checkout.show', $order))
            ->assertRedirect(route('products.index'));
    }

    public function test_payment_step_can_go_back_to_address_selection_and_restore_stock(): void
    {
        $product = $this->product(stock: 5, priceCents: 2800);

        $order = Order::query()->create([
            'status' => 'awaiting_payment',
            'currency' => 'EUR',
            'subtotal_cents' => 5600,
            'shipping_cents' => 0,
            'total_cents' => 5600,
            'customer_name' => 'Taylor Forge',
            'customer_email' => 'taylor@example.test',
            'customer_phone' => '+1-555-0100',
            'shipping_address_line_1' => '123 Ember Street',
            'shipping_address_line_2' => 'Unit B',
            'shipping_city' => 'Ironvale',
            'shipping_state' => 'CA',
            'shipping_postal_code' => '90210',
            'shipping_country' => 'US',
            'placed_at' => null,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'product_size' => 'L',
            'unit_price_cents' => $product->price_cents,
            'quantity' => 2,
            'line_total_cents' => 5600,
        ]);

        $product->decrement('stock', 2);

        $this->withSession([
            'cart.items' => [$product->id.':L' => 2],
            'checkout.last_order_email' => $order->customer_email,
            'checkout.pending_order_id' => $order->id,
        ])->post(route('checkout.back'))
            ->assertRedirect(route('checkout.index'));

        $this->assertSoftDeleted('orders', [
            'id' => $order->id,
        ]);
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertSame([$product->id.':L' => 2], session('cart.items', []));
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

    public function test_shipping_page_reopens_pending_checkout_instead_of_redirecting_to_payment(): void
    {
        $product = $this->product(stock: 5, priceCents: 2800);

        $order = Order::query()->create([
            'status' => 'awaiting_payment',
            'currency' => 'EUR',
            'subtotal_cents' => 5600,
            'shipping_cents' => 0,
            'total_cents' => 5600,
            'customer_name' => 'Taylor Forge',
            'customer_email' => 'taylor@example.test',
            'customer_phone' => '+1-555-0100',
            'shipping_address_line_1' => '123 Ember Street',
            'shipping_address_line_2' => 'Unit B',
            'shipping_city' => 'Ironvale',
            'shipping_state' => 'CA',
            'shipping_postal_code' => '90210',
            'shipping_country' => 'US',
            'placed_at' => null,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'product_size' => 'L',
            'unit_price_cents' => $product->price_cents,
            'quantity' => 2,
            'line_total_cents' => 5600,
        ]);

        $product->decrement('stock', 2);

        $this->withSession([
            'cart.items' => [$product->id.':L' => 2],
            'checkout.last_order_email' => $order->customer_email,
            'checkout.pending_order_id' => $order->id,
        ])->get(route('checkout.index'))
            ->assertOk();

        $this->assertSoftDeleted('orders', [
            'id' => $order->id,
        ]);
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertNull(session('checkout.pending_order_id'));
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

    public function test_authenticated_checkout_prefills_from_selected_saved_shipping_address(): void
    {
        $user = User::factory()->create([
            'name' => 'Cookie',
            'email' => 'cookie@example.test',
        ]);
        $product = $this->product();
        $address = ShippingAddress::query()->create([
            'user_id' => $user->id,
            'label' => 'Studio',
            'recipient_name' => 'Cookie Destroyer',
            'phone' => '+359-88-111-2222',
            'address_line_1' => '13 Void Circuit',
            'address_line_2' => 'Suite A',
            'city' => 'Sofia',
            'state' => 'Sofia City',
            'postal_code' => '1000',
            'country' => 'BG',
            'is_default' => true,
        ]);

        $this->actingAs($user)
            ->withSession([
                'cart.items' => [$product->id.':M' => 1],
            ])
            ->post(route('checkout.address'), ['address' => $address->id])
            ->assertRedirect(route('checkout.index'));

        $this->actingAs($user)
            ->withSession([
                'cart.items' => [$product->id.':M' => 1],
                'checkout.selected_shipping_address_id' => $address->id,
            ])
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('Studio · Default')
            ->assertSee('Cookie Destroyer')
            ->assertSee('13 Void Circuit')
            ->assertSee('1000');
    }

    public function test_selecting_a_saved_shipping_address_redirects_back_to_clean_checkout_url(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $address = ShippingAddress::query()->create([
            'user_id' => $user->id,
            'label' => 'Studio',
            'recipient_name' => 'Cookie Destroyer',
            'phone' => '+359-88-111-2222',
            'address_line_1' => '13 Void Circuit',
            'address_line_2' => 'Suite A',
            'city' => 'Sofia',
            'state' => 'Sofia City',
            'postal_code' => '1000',
            'country' => 'BG',
            'is_default' => true,
        ]);

        $this->actingAs($user)
            ->withSession([
                'cart.items' => [$product->id.':M' => 1],
            ])
            ->post(route('checkout.address'), ['address' => $address->id])
            ->assertRedirect(route('checkout.index'));
    }

    public function test_authenticated_checkout_preselects_the_default_saved_shipping_address_and_hides_manual_entry(): void
    {
        $user = User::factory()->create([
            'name' => 'Cookie',
            'email' => 'cookie@example.test',
        ]);
        $product = $this->product();

        ShippingAddress::query()->create([
            'user_id' => $user->id,
            'label' => 'Office',
            'recipient_name' => 'Cookie Office',
            'phone' => '+359-88-000-0001',
            'address_line_1' => '1 Office Street',
            'address_line_2' => null,
            'city' => 'Varna',
            'state' => null,
            'postal_code' => '9000',
            'country' => 'BG',
            'is_default' => false,
        ]);

        ShippingAddress::query()->create([
            'user_id' => $user->id,
            'label' => 'Home',
            'recipient_name' => 'Cookie Home',
            'phone' => '+359-88-000-0002',
            'address_line_1' => '13 Void Circuit',
            'address_line_2' => 'Suite A',
            'city' => 'Sofia',
            'state' => 'Sofia City',
            'postal_code' => '1000',
            'country' => 'BG',
            'is_default' => true,
        ]);

        $this->actingAs($user)
            ->withSession([
                'cart.items' => [$product->id.':M' => 1],
            ])
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('Home · Default')
            ->assertSee('Cookie Home')
            ->assertSee('Add new address')
            ->assertDontSee('Address line 1');
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
