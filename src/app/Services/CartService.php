<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CartService
{
    private const SESSION_KEY = 'cart.items';

    /**
     * Get all cart line items with product data.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function items(): Collection
    {
        $storedItems = $this->storedItems();

        if ($storedItems === []) {
            return collect();
        }

        $products = Product::query()
            ->with('category')
            ->publiclyVisible()
            ->whereIn('id', collect($storedItems)->pluck('product_id')->unique()->all())
            ->get()
            ->keyBy('id');

        $items = collect();

        foreach ($storedItems as $storedItem) {
            $product = $products->get($storedItem['product_id']);

            if (! $product) {
                continue;
            }

            $items->push([
                'product' => $product,
                'size' => $storedItem['size'],
                'quantity' => $storedItem['quantity'],
                'key' => $this->lineKey($product->id, $storedItem['size']),
                'line_total_cents' => $product->price_cents * $storedItem['quantity'],
            ]);
        }

        if ($items->count() !== count($storedItems)) {
            $this->replaceStoredItems(
                $items->mapWithKeys(fn (array $item) => [
                    $item['key'] => $item['quantity'],
                ])->all()
            );
        }

        return $items->values();
    }

    /**
     * Add a product to the cart.
     *
     * @throws ValidationException
     */
    public function add(Product $product, string $size, int $quantity): void
    {
        if (! $product->is_active) {
            throw ValidationException::withMessages([
                'quantity' => 'This product is not available.',
            ]);
        }

        $size = $this->normalizeSize($size);
        $this->guardSizeAvailability($product, $size);

        $items = $this->storedItems();
        $lineKey = $this->lineKey($product->id, $size);
        $currentQuantity = (int) Arr::get($items, $lineKey.'.quantity', 0);
        $newQuantity = $currentQuantity + $quantity;

        $this->guardStock($product, $newQuantity);

        $items[$lineKey] = [
            'product_id' => $product->id,
            'size' => $size,
            'quantity' => $newQuantity,
        ];

        $this->replaceStoredItems($items);
    }

    /**
     * Update a cart line item.
     *
     * @throws ValidationException
     */
    public function update(Product $product, string $size, int $quantity): void
    {
        $size = $this->normalizeSize($size);
        $items = $this->storedItems();
        $lineKey = $this->lineKey($product->id, $size);

        if ($quantity === 0) {
            unset($items[$lineKey]);
            $this->replaceStoredItems($items);

            return;
        }

        $this->guardSizeAvailability($product, $size);
        $this->guardStock($product, $quantity);

        $items[$lineKey] = [
            'product_id' => $product->id,
            'size' => $size,
            'quantity' => $quantity,
        ];

        $this->replaceStoredItems($items);
    }

    /**
     * Remove a product from the cart.
     */
    public function remove(Product $product, string $size): void
    {
        $items = $this->storedItems();
        unset($items[$this->lineKey($product->id, $this->normalizeSize($size))]);

        $this->replaceStoredItems($items);
    }

    /**
     * Get the current cart item count.
     */
    public function itemCount(): int
    {
        return (int) $this->items()->sum('quantity');
    }

    /**
     * Get the current cart subtotal in cents.
     */
    public function subtotalCents(): int
    {
        return (int) $this->items()->sum('line_total_cents');
    }

    /**
     * Remove all items from the cart.
     */
    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
        session()->forget('cart.discount_code');
    }

    /**
     * Get the raw stored cart items.
     *
     * @return array<string, array{product_id: int, size: string, quantity: int}>
     */
    private function storedItems(): array
    {
        return collect(session()->get(self::SESSION_KEY, []))
            ->mapWithKeys(function ($value, $key): array {
                if (is_array($value)) {
                    $productId = (int) ($value['product_id'] ?? 0);
                    $size = $this->normalizeSize((string) ($value['size'] ?? Product::DEFAULT_SHIRT_SIZE));
                    $quantity = (int) ($value['quantity'] ?? 0);

                    return $productId > 0
                        ? [$this->lineKey($productId, $size) => [
                            'product_id' => $productId,
                            'size' => $size,
                            'quantity' => $quantity,
                        ]]
                        : [];
                }

                [$productId, $size] = $this->parseLineKey((string) $key);

                return $productId > 0
                    ? [$this->lineKey($productId, $size) => [
                        'product_id' => $productId,
                        'size' => $size,
                        'quantity' => (int) $value,
                    ]]
                    : [];
            })
            ->filter(fn (array $item) => $item['quantity'] > 0)
            ->all();
    }

    /**
     * Persist cart items in the session.
     *
     * @param  array<string, int>  $items
     */
    private function replaceStoredItems(array $items): void
    {
        session()->put(
            self::SESSION_KEY,
            collect($items)->mapWithKeys(fn (array $item, string $key) => [
                $key => (int) $item['quantity'],
            ])->all()
        );
    }

    /**
     * Ensure a requested quantity does not exceed stock.
     *
     * @throws ValidationException
     */
    private function guardStock(Product $product, int $quantity): void
    {
        if ($product->stock < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Requested quantity exceeds available stock.',
            ]);
        }
    }

    /**
     * Ensure a requested size is available for purchase.
     *
     * @throws ValidationException
     */
    private function guardSizeAvailability(Product $product, string $size): void
    {
        if (! $product->isShirtSizeAvailable($size)) {
            throw ValidationException::withMessages([
                'size' => 'Selected shirt size is sold out.',
            ]);
        }
    }

    private function lineKey(int $productId, string $size): string
    {
        return $productId.':'.$this->normalizeSize($size);
    }

    /**
     * @return array{0: int, 1: string}
     */
    private function parseLineKey(string $key): array
    {
        [$productId, $size] = array_pad(explode(':', $key, 2), 2, Product::DEFAULT_SHIRT_SIZE);

        return [(int) $productId, $this->normalizeSize((string) $size)];
    }

    private function normalizeSize(string $size): string
    {
        $size = strtoupper(trim($size));

        return in_array($size, Product::shirtSizes(), true)
            ? $size
            : Product::DEFAULT_SHIRT_SIZE;
    }
}
