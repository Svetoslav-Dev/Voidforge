<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\AdminCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCategoryController extends Controller
{
    /**
     * Display the admin catalog listing.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        return view('admin.categories.listing', [
            'categories' => Category::query()
                ->withTrashed()
                ->withCount('products')
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($builder) use ($search) {
                        $builder
                            ->where('name', 'like', '%'.$search.'%')
                            ->orWhere('slug', 'like', '%'.$search.'%')
                            ->orWhere('description', 'like', '%'.$search.'%');
                    });
                })
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    /**
     * Show the create catalog form.
     */
    public function create(): View
    {
        return view('admin.categories.create');
    }

    /**
     * Persist a new catalog.
     */
    public function store(StoreCategoryRequest $request, AdminCategoryService $categories): RedirectResponse
    {
        $categories->create($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Catalog created.');
    }

    /**
     * Show the edit catalog form.
     */
    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update an existing catalog.
     */
    public function update(UpdateCategoryRequest $request, Category $category, AdminCategoryService $categories): RedirectResponse
    {
        $categories->update($category, $request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Catalog updated.');
    }

    /**
     * Soft delete the given catalog.
     */
    public function destroy(Category $category, AdminCategoryService $categories): RedirectResponse
    {
        $categories->delete($category);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Catalog archived.');
    }

    /**
     * Restore a soft-deleted catalog.
     */
    public function restore(string $category, AdminCategoryService $categories): RedirectResponse
    {
        $categories->restore(
            Category::query()->withTrashed()->where('slug', $category)->firstOrFail()
        );

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Catalog restored.');
    }
}
