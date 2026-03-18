<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartService $cart;

    protected function setUp(): void
    {
        parent::setUp();

        session()->start();
        $this->cart = app(CartService::class);
    }

    public function test_it_calculates_item_count_and_subtotal_from_session_items(): void
    {
        $first = $this->product(name: 'Forge Mark Tee', sku: 'VF-TEE-001', priceCents: 2800, stock: 10);
        $second = $this->product(name: 'Null Crest Tee', sku: 'VF-TEE-002', priceCents: 3200, stock: 10);

        $this->cart->add($first, 'M', 2);
        $this->cart->add($second, 'L', 1);

        $items = $this->cart->items();

        $this->assertCount(2, $items);
        $this->assertSame(3, $this->cart->itemCount());
        $this->assertSame(8800, $this->cart->subtotalCents());
        $this->assertSame('M', $items->first()['size']);
    }

    public function test_it_removes_missing_or_unavailable_products_from_the_stored_cart(): void
    {
        $product = $this->product();

        session()->put('cart.items', [
            $product->id.':M' => 2,
            '999999:M' => 3,
        ]);

        $product->delete();

        $items = $this->cart->items();

        $this->assertCount(0, $items);
        $this->assertSame([], session('cart.items'));
    }

    public function test_it_rejects_quantities_above_available_stock(): void
    {
        $product = $this->product(stock: 2);

        $this->expectException(ValidationException::class);

        $this->cart->add($product, 'M', 3);
    }

    private function product(
        string $name = 'Deep Stack Tee',
        string $sku = 'VF-TEE-003',
        int $priceCents = 3000,
        int $stock = 25
    ): Product {
        $category = Category::query()->firstOrCreate([
            'slug' => 'void-tees',
        ], [
            'name' => 'Void Tees',
            'description' => 'Test category.',
        ]);

        return Product::factory()->create([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'sku' => $sku,
            'price_cents' => $priceCents,
            'stock' => $stock,
            'is_active' => true,
        ]);
    }
}
