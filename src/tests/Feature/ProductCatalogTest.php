<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_page_hides_items_from_archived_categories(): void
    {
        $visibleCategory = Category::factory()->create(['name' => 'Visible', 'slug' => 'visible']);
        $archivedCategory = Category::factory()->create(['name' => 'Archived', 'slug' => 'archived']);

        Product::factory()->create([
            'category_id' => $visibleCategory->id,
            'name' => 'Visible Item',
            'slug' => 'visible-item',
        ]);

        Product::factory()->create([
            'category_id' => $archivedCategory->id,
            'name' => 'Hidden Item',
            'slug' => 'hidden-item',
        ]);

        $archivedCategory->delete();

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Visible Item')
            ->assertDontSee('Hidden Item');
    }

    public function test_product_page_returns_not_found_for_items_in_archived_categories(): void
    {
        $category = Category::factory()->create(['name' => 'Archived', 'slug' => 'archived']);

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Hidden Item',
            'slug' => 'hidden-item',
        ]);

        $category->delete();

        $this->get(route('products.show', $product))
            ->assertNotFound();
    }
}
