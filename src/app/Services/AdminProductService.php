<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminProductService
{
    /**
     * Create a product from validated admin input.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        return Product::query()->create($this->normalizedData($data));
    }

    /**
     * Update a product from validated admin input.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($this->normalizedData($data, $product));

        return $product->fresh('category');
    }

    /**
     * Soft delete a product.
     */
    public function delete(Product $product): void
    {
        $product->delete();
    }

    /**
     * Restore a soft-deleted product.
     */
    public function restore(Product $product): Product
    {
        $product->restore();

        return $product->fresh('category');
    }

    /**
     * Normalize validated request data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizedData(array $data, ?Product $product = null): array
    {
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['price_cents'] = (int) round(((float) ($data['price'] ?? 0)) * 100);
        $data['image_path'] = $this->storeImage($data['image'] ?? null) ?? $product?->image_path;
        $data['back_image_path'] = $this->storeImage($data['back_image'] ?? null) ?? $product?->back_image_path;
        $data['disabled_sizes'] = collect($data['disabled_sizes'] ?? [])
            ->map(fn (mixed $size) => strtoupper((string) $size))
            ->filter(fn (string $size) => in_array($size, Product::shirtSizes(), true))
            ->unique()
            ->values()
            ->all();
        unset($data['price']);
        unset($data['image']);
        unset($data['back_image']);

        return $data;
    }

    private function storeImage(mixed $image): ?string
    {
        if (! $image instanceof UploadedFile) {
            return null;
        }

        $filename = Str::uuid()->toString().'.'.$image->getClientOriginalExtension();
        Storage::disk('public')->putFileAs('item-images', $image, $filename);

        return 'item-images/'.$filename;
    }
}
