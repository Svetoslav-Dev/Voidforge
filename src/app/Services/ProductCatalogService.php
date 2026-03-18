<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductCatalogService
{
    /**
     * Get all categories ordered for catalog filters.
     *
     * @return Collection<int, Category>
     */
    public function categories(): Collection
    {
        return Category::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();
    }

    /**
     * Paginate active products with an optional category filter.
     */
    public function paginatedProducts(?string $categorySlug = null, int $perPage = 12): LengthAwarePaginator
    {
        return Product::query()
            ->with('category')
            ->publiclyVisible()
            ->when(
                $categorySlug,
                fn ($query) => $query->whereHas(
                    'category',
                    fn ($categoryQuery) => $categoryQuery->where('slug', $categorySlug)
                )
            )
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Find a single active product for the public catalog.
     */
    public function productBySlug(string $slug): Product
    {
        $product = Product::query()
            ->with('category')
            ->publiclyVisible()
            ->where('slug', $slug)
            ->first();

        if (! $product) {
            throw (new ModelNotFoundException())->setModel(Product::class, [$slug]);
        }

        return $product;
    }
}
