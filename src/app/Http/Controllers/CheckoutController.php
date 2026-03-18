<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checkout\StoreCheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page.
     */
    public function index(Request $request, CartService $cart): View|RedirectResponse
    {
        if ($cart->items()->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('status', 'Your cart is empty.');
        }

        return view('checkout.form', [
            'items' => $cart->items(),
            'subtotalCents' => $cart->subtotalCents(),
            'europeanCountries' => StoreCheckoutRequest::europeanCountries(),
            'checkoutDefaults' => [
                'customer_name' => (string) optional($request->user())->name,
                'customer_email' => (string) optional($request->user())->email,
                'customer_phone' => '',
                'shipping_address_line_1' => '',
                'shipping_address_line_2' => '',
                'shipping_city' => '',
                'shipping_state' => '',
                'shipping_postal_code' => '',
                'shipping_country' => 'BG',
            ],
        ]);
    }

    /**
     * Create an order from the current cart.
     */
    public function store(StoreCheckoutRequest $request, CheckoutService $checkout): RedirectResponse
    {
        $order = $checkout->placeOrder($request->validated(), $request->user());

        return redirect()
            ->route('checkout.show', $order)
            ->with('status', 'Order placed successfully.');
    }

    /**
     * Display the order confirmation page.
     */
    public function show(Request $request, Order $order): View
    {
        if (
            $request->user()
            && $order->user_id !== null
            && $order->user_id !== $request->user()->id
        ) {
            abort(404);
        }

        if (
            ! $request->user()
            && $order->customer_email !== $request->session()->get('checkout.last_order_email')
        ) {
            abort(404);
        }

        return view('checkout.confirmation', [
            'order' => $order->load('items'),
        ]);
    }
}
