<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cart
    ) {
    }

    /**
     * Display the cart.
     */
    public function index(): View
    {
        return view('cart.overview', [
            'items' => $this->cart->items(),
            'itemCount' => $this->cart->itemCount(),
            'subtotalCents' => $this->cart->subtotalCents(),
        ]);
    }

    /**
     * Add a product to the cart.
     */
    public function store(AddToCartRequest $request, Product $product): RedirectResponse
    {
        $this->cart->add(
            $product,
            (string) $request->validated('size'),
            (int) $request->validated('quantity')
        );

        return redirect()
            ->back()
            ->with('status', $product->name.' added to cart.');
    }

    /**
     * Update a cart item quantity.
     */
    public function update(UpdateCartItemRequest $request, Product $product): RedirectResponse
    {
        $quantity = (int) $request->validated('quantity');
        $size = (string) $request->validated('size');

        $this->cart->update($product, $size, $quantity);

        return redirect()
            ->route('cart.index')
            ->with('status', $quantity === 0 ? $product->name.' removed from cart.' : 'Cart updated.');
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'size' => ['required', 'string', Rule::in(Product::shirtSizes())],
        ]);

        $this->cart->remove($product, (string) $validated['size']);

        return redirect()
            ->route('cart.index')
            ->with('status', $product->name.' removed from cart.');
    }
}
