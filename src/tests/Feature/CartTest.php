<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DiscountCode;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    public function test_a_product_can_be_added_to_the_cart(): void
    {
        $product = $this->product();

        $response = $this->post(route('cart.store', $product), [
            'quantity' => 2,
            'size' => 'M',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $this->assertSame([
            $product->id.':M' => 2,
        ], session('cart.items'));

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Forge Mark Tee')
            ->assertSee('Size: M')
            ->assertSee('56.00 EUR');
    }

    public function test_a_product_can_be_added_to_the_cart_via_ajax(): void
    {
        $product = $this->product();

        $this->postJson(route('cart.store', $product), [
            'quantity' => 1,
            'size' => 'M',
        ])->assertCreated()
            ->assertJson([
                'status' => 'Forge Mark Tee added to cart.',
                'item_count' => 1,
            ]);
    }

    public function test_a_cart_item_quantity_can_be_updated(): void
    {
        $product = $this->product();

        $this->withSession([
            'cart.items' => [$product->id.':M' => 1],
        ])->patch(route('cart.update', $product), [
            'quantity' => 3,
            'size' => 'M',
        ])->assertRedirect(route('cart.index'));

        $this->assertSame([
            $product->id.':M' => 3,
        ], session('cart.items'));
    }

    public function test_a_cart_item_can_be_removed_by_setting_quantity_to_zero(): void
    {
        $product = $this->product();

        $this->withSession([
            'cart.items' => [$product->id.':M' => 2],
        ])->patch(route('cart.update', $product), [
            'quantity' => 0,
            'size' => 'M',
        ])->assertRedirect(route('cart.index'));

        $this->assertSame([], session('cart.items'));
    }

    public function test_a_cart_item_can_be_removed_with_the_delete_route(): void
    {
        $product = $this->product();

        $this->withSession([
            'cart.items' => [$product->id.':M' => 2],
        ])->delete(route('cart.destroy', $product), [
            'size' => 'M',
        ])
            ->assertRedirect(route('cart.index'));

        $this->assertSame([], session('cart.items'));
    }

    public function test_the_cart_prevents_quantities_above_stock(): void
    {
        $product = $this->product(stock: 2);

        $response = $this->post(route('cart.store', $product), [
            'quantity' => 3,
            'size' => 'M',
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertSame([], session('cart.items', []));
    }

    public function test_the_cart_prevents_adding_a_disabled_shirt_size(): void
    {
        $product = $this->product();
        $product->update([
            'disabled_sizes' => ['L'],
        ]);

        $response = $this->post(route('cart.store', $product), [
            'quantity' => 1,
            'size' => 'L',
        ]);

        $response->assertSessionHasErrors('size');
        $this->assertSame([], session('cart.items', []));
    }

    public function test_a_discount_code_can_be_applied_and_removed_from_the_cart(): void
    {
        $product = $this->product();
        DiscountCode::query()->create([
            'code' => 'WELCOME10',
            'type' => 'percent',
            'amount' => 10,
            'is_active' => true,
        ]);

        $this->withSession([
            'cart.items' => [$product->id.':M' => 2],
        ])->post(route('cart.discount.store'), [
            'code' => 'welcome10',
        ])->assertRedirect(route('cart.index'));

        $this->assertSame('WELCOME10', session('cart.discount_code'));

        $this->withSession([
            'cart.items' => [$product->id.':M' => 2],
            'cart.discount_code' => 'WELCOME10',
        ])->delete(route('cart.discount.destroy'))
            ->assertRedirect(route('cart.index'));

        $this->assertNull(session('cart.discount_code'));
    }

    public function test_cart_page_contains_policy_links_before_shipping(): void
    {
        $product = $this->product();

        $this->withSession([
            'cart.items' => [$product->id.':M' => 1],
        ])->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Before shipping')
            ->assertSee(route('legal.terms'), false)
            ->assertSee(route('legal.privacy'), false)
            ->assertSee(route('legal.returns'), false)
            ->assertSee(route('legal.contact'), false);
    }

    private function product(int $stock = 25): Product
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
            'price_cents' => 2800,
            'stock' => $stock,
            'is_active' => true,
        ]);
    }
}
