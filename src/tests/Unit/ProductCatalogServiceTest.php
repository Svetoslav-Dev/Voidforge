<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductCatalogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductCatalogService $catalog;

    protected function setUp(): void
    {
        parent::setUp();

        $this->catalog = app(ProductCatalogService::class);
    }

    public function test_it_returns_categories_in_name_order(): void
    {
        Category::factory()->create(['name' => 'Signals', 'slug' => 'signals']);
        Category::factory()->create(['name' => 'Artifacts', 'slug' => 'artifacts']);
        Category::factory()->create(['name' => 'Blackouts', 'slug' => 'blackouts']);

        $names = $this->catalog->categories()->pluck('name')->all();

        $this->assertSame(['Artifacts', 'Blackouts', 'Signals'], $names);
    }

    public function test_it_only_returns_active_products_and_applies_category_filters(): void
    {
        $tops = Category::factory()->create(['name' => 'Tops', 'slug' => 'tops']);
        $bottoms = Category::factory()->create(['name' => 'Bottoms', 'slug' => 'bottoms']);

        Product::factory()->create([
            'category_id' => $tops->id,
            'name' => 'Active Top',
            'slug' => 'active-top',
            'is_active' => true,
        ]);
        Product::factory()->create([
            'category_id' => $tops->id,
            'name' => 'Inactive Top',
            'slug' => 'inactive-top',
            'is_active' => false,
        ]);
        Product::factory()->create([
            'category_id' => $bottoms->id,
            'name' => 'Active Bottom',
            'slug' => 'active-bottom',
            'is_active' => true,
        ]);

        $filtered = $this->catalog->paginatedProducts('tops', 12);

        $this->assertSame(['Active Top'], $filtered->getCollection()->pluck('name')->all());
    }

    public function test_it_hides_products_from_archived_categories(): void
    {
        $visibleCategory = Category::factory()->create(['name' => 'Visible', 'slug' => 'visible']);
        $archivedCategory = Category::factory()->create(['name' => 'Archived', 'slug' => 'archived']);
        $archivedCategory->delete();

        Product::factory()->create([
            'category_id' => $visibleCategory->id,
            'name' => 'Visible Item',
            'slug' => 'visible-item',
            'is_active' => true,
        ]);
        Product::factory()->create([
            'category_id' => $archivedCategory->id,
            'name' => 'Archived Category Item',
            'slug' => 'archived-category-item',
            'is_active' => true,
        ]);

        $products = $this->catalog->paginatedProducts(null, 12);

        $this->assertSame(['Visible Item'], $products->getCollection()->pluck('name')->all());
    }

    public function test_it_finds_an_active_product_by_slug(): void
    {
        $product = Product::factory()->create([
            'name' => 'Ghost Relay Tee',
            'slug' => 'ghost-relay-tee',
            'is_active' => true,
        ]);

        $resolved = $this->catalog->productBySlug('ghost-relay-tee');

        $this->assertSame($product->id, $resolved->id);
    }

    public function test_it_throws_for_inactive_products_when_resolving_by_slug(): void
    {
        Product::factory()->create([
            'slug' => 'hidden-item',
            'is_active' => false,
        ]);

        $this->expectException(ModelNotFoundException::class);

        $this->catalog->productBySlug('hidden-item');
    }

    public function test_it_throws_for_products_in_archived_categories_when_resolving_by_slug(): void
    {
        $category = Category::factory()->create(['slug' => 'archived-category']);
        $category->delete();

        Product::factory()->create([
            'category_id' => $category->id,
            'slug' => 'hidden-by-category',
            'is_active' => true,
        ]);

        $this->expectException(ModelNotFoundException::class);

        $this->catalog->productBySlug('hidden-by-category');
    }
}
