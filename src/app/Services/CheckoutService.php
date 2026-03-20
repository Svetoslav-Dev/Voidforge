<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly CartService $cart,
        private readonly DiscountCodeService $discountCodes
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
            $discountCode = $this->discountCodes->reserveCurrent($subtotalCents);
            $discountCents = $discountCode?->discountCentsFor($subtotalCents) ?? 0;

            $order = Order::query()->create([
                ...$customerData,
                'user_id' => $user?->id,
                'discount_code_id' => $discountCode?->id,
                'status' => 'awaiting_payment',
                'currency' => 'EUR',
                'subtotal_cents' => $subtotalCents,
                'shipping_cents' => 0,
                'discount_code' => $discountCode?->code,
                'discount_cents' => $discountCents,
                'total_cents' => max(0, $subtotalCents - $discountCents),
                'placed_at' => null,
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

            if ($discountCode) {
                session()->put('cart.discount_code', $discountCode->code);
            }

            session()->put('checkout.last_order_email', $order->customer_email);

            return $order->load('items');
        });
    }

    /**
     * Reopen a pending checkout by restoring stock and removing the unpaid order.
     */
    public function reopenPendingOrder(Order $order): void
    {
        if ($order->placed_at !== null || $order->status === 'paid') {
            return;
        }

        DB::transaction(function () use ($order): void {
            $order->loadMissing('items');

            $products = Product::query()
                ->lockForUpdate()
                ->whereIn('id', $order->items->pluck('product_id')->filter()->all())
                ->get()
                ->keyBy('id');

            /** @var OrderItem $item */
            foreach ($order->items as $item) {
                $product = $products->get($item->product_id);

                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }

            $this->discountCodes->release($order->discountCodeModel);
            if ($order->discount_code) {
                session()->put('cart.discount_code', $order->discount_code);
            }

            $order->payments()->delete();
            $order->items()->delete();
            $order->delete();
        });
    }
}
