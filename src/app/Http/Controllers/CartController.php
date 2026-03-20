<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\ApplyDiscountCodeRequest;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Models\Product;
use App\Services\CartService;
use App\Services\DiscountCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly DiscountCodeService $discountCodes
    ) {
    }

    /**
     * Display the cart.
     */
    public function index(): View
    {
        $subtotalCents = $this->cart->subtotalCents();

        return view('cart.overview', [
            'items' => $this->cart->items(),
            'itemCount' => $this->cart->itemCount(),
            'subtotalCents' => $subtotalCents,
            'discountSummary' => $this->discountCodes->summary($subtotalCents),
        ]);
    }

    /**
     * Add a product to the cart.
     */
    public function store(AddToCartRequest $request, Product $product): RedirectResponse|JsonResponse
    {
        $this->cart->add(
            $product,
            (string) $request->validated('size'),
            (int) $request->validated('quantity')
        );

        $status = $product->name.' added to cart.';

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'status' => $status,
                'item_count' => $this->cart->itemCount(),
            ], 201);
        }

        return redirect()
            ->back()
            ->with('status', $status);
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

    /**
     * Apply a discount code to the current cart.
     */
    public function applyDiscount(ApplyDiscountCodeRequest $request): RedirectResponse
    {
        $discountCode = $this->discountCodes->apply(
            (string) $request->validated('code'),
            $this->cart->subtotalCents()
        );

        return redirect()
            ->route('cart.index')
            ->with('status', 'Discount code '.$discountCode->code.' applied.');
    }

    /**
     * Remove the current discount code from the cart.
     */
    public function removeDiscount(): RedirectResponse
    {
        $this->discountCodes->clear();

        return redirect()
            ->route('cart.index')
            ->with('status', 'Discount code removed.');
    }
}
