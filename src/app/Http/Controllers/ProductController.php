<?php

namespace App\Http\Controllers;

use App\Services\ProductCatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductCatalogService $catalog
    ) {
    }

    /**
     * Display the public product catalog.
     */
    public function index(Request $request): View
    {
        $selectedCategory = $request->query('category');

        return view('products.catalog', [
            'categories' => $this->catalog->categories(),
            'products' => $this->catalog->paginatedProducts($selectedCategory),
            'selectedCategory' => $selectedCategory,
        ]);
    }

    /**
     * Display a single product.
     */
    public function show(string $product): View
    {
        return view('products.details', [
            'product' => $this->catalog->productBySlug($product),
        ]);
    }
}
