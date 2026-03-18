<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\AdminProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminProductController extends Controller
{
    /**
     * Display the admin product listing.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        return view('admin.products.listing', [
            'products' => Product::query()
                ->with('category')
                ->withTrashed()
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($builder) use ($search) {
                        $builder
                            ->where('name', 'like', '%'.$search.'%')
                            ->orWhere('slug', 'like', '%'.$search.'%')
                            ->orWhere('sku', 'like', '%'.$search.'%');
                    });
                })
                ->orderByDesc('created_at')
                ->paginate(15)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    /**
     * Show the create product form.
     */
    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Persist a new product.
     */
    public function store(StoreProductRequest $request, AdminProductService $products): RedirectResponse
    {
        $product = $products->create($request->validated());

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('status', 'Item created.');
    }

    /**
     * Show the edit product form.
     */
    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Update an existing product.
     */
    public function update(UpdateProductRequest $request, Product $product, AdminProductService $products): RedirectResponse
    {
        $products->update($product, $request->validated());

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('status', 'Item updated.');
    }

    /**
     * Soft delete the given product.
     */
    public function destroy(Product $product, AdminProductService $products): RedirectResponse
    {
        $products->delete($product);

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Item archived.');
    }

    /**
     * Restore a soft-deleted product.
     */
    public function restore(string $product, AdminProductService $products): RedirectResponse
    {
        $products->restore(
            Product::query()->withTrashed()->where('slug', $product)->firstOrFail()
        );

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Item restored.');
    }
}
