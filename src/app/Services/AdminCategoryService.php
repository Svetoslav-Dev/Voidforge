<?php

namespace App\Services;

use App\Models\Category;

class AdminCategoryService
{
    /**
     * Create a catalog from validated admin input.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Category
    {
        return Category::query()->create($data);
    }

    /**
     * Update a catalog from validated admin input.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->fresh();
    }

    /**
     * Soft delete a catalog.
     */
    public function delete(Category $category): void
    {
        $category->delete();
    }

    /**
     * Restore a soft-deleted catalog.
     */
    public function restore(Category $category): Category
    {
        $category->restore();

        return $category->fresh();
    }
}
