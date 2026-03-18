<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Services\AdminProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private AdminProductService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AdminProductService::class);
    }

    public function test_it_defaults_new_products_to_inactive_when_flag_is_missing(): void
    {
        $category = Category::factory()->create();

        $product = $this->service->create([
            'category_id' => $category->id,
            'name' => 'Admin Draft Item',
            'slug' => 'admin-draft-item',
            'sku' => 'VF-DRAFT-001',
            'description' => 'Draft item.',
            'price_cents' => 3100,
            'stock' => 7,
        ]);

        $this->assertFalse($product->is_active);
    }

    public function test_it_returns_the_updated_product_with_its_category_loaded(): void
    {
        $oldCategory = Category::factory()->create(['name' => 'Old', 'slug' => 'old']);
        $newCategory = Category::factory()->create(['name' => 'New', 'slug' => 'new']);
        $product = Product::factory()->create([
            'category_id' => $oldCategory->id,
            'slug' => 'existing-admin-item',
            'sku' => 'VF-ADMIN-100',
        ]);

        $updated = $this->service->update($product, [
            'category_id' => $newCategory->id,
            'name' => 'Updated Admin Item',
            'slug' => 'updated-admin-item',
            'sku' => 'VF-ADMIN-100',
            'description' => 'Updated item.',
            'price_cents' => 3600,
            'stock' => 12,
            'is_active' => 1,
        ]);

        $this->assertTrue($updated->relationLoaded('category'));
        $this->assertSame($newCategory->id, $updated->category->id);
        $this->assertSame(1, (int) $updated->is_active);
    }

    public function test_it_soft_deletes_products(): void
    {
        $product = Product::factory()->create();

        $this->service->delete($product);

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }

    public function test_it_normalizes_disabled_sizes_when_saving_a_product(): void
    {
        $category = Category::factory()->create();

        $product = $this->service->create([
            'category_id' => $category->id,
            'name' => 'Sized Shirt',
            'slug' => 'sized-shirt',
            'sku' => 'VF-SIZED-001',
            'description' => 'Sized shirt.',
            'price' => 31.00,
            'stock' => 7,
            'is_active' => 1,
            'disabled_sizes' => ['m', 'XL', 'invalid', 'M'],
        ]);

        $this->assertSame(['M', 'XL'], $product->disabledShirtSizes());
    }
}
