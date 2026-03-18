<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly CartService $cart
    ) {
    }

    /**
     * Create an order from the current cart contents.
     *
     * @param  array<string, mixed>  $customerData
     * @throws ValidationException
     */
    public function placeOrder(array $customerData, ?User $user = null): Order
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Your cart is empty.',
            ]);
        }

        return DB::transaction(function () use ($customerData, $items, $user): Order {
            $products = Product::query()
                ->lockForUpdate()
                ->whereIn('id', $items->pluck('product.id')->all())
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                /** @var Product|null $product */
                $product = $products->get($item['product']->id);

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'One of the selected products is no longer available.',
                    ]);
                }

                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'cart' => 'One of the selected products no longer has enough stock.',
                    ]);
                }
            }

            $subtotalCents = (int) $items->sum('line_total_cents');

            $order = Order::query()->create([
                ...$customerData,
                'user_id' => $user?->id,
                'status' => 'pending',
                'currency' => 'EUR',
                'subtotal_cents' => $subtotalCents,
                'shipping_cents' => 0,
                'total_cents' => $subtotalCents,
                'placed_at' => now(),
            ]);

            foreach ($items as $item) {
                /** @var Product $product */
                $product = $products->get($item['product']->id);

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'product_size' => $item['size'],
                    'unit_price_cents' => $product->price_cents,
                    'quantity' => $item['quantity'],
                    'line_total_cents' => $product->price_cents * $item['quantity'],
                ]);

                $product->decrement('stock', $item['quantity']);
            }

            $this->cart->clear();
            session()->put('checkout.last_order_email', $order->customer_email);

            return $order->load('items');
        });
    }
}
