<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checkout\StoreCheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\DiscountCodeService;
use App\Services\ReceiptPdfService;
use App\Services\StripePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page.
     */
    public function index(
        Request $request,
        CartService $cart,
        DiscountCodeService $discountCodes,
        StripePaymentService $stripe,
        CheckoutService $checkout
    ): View|RedirectResponse
    {
        if ($request->query('stripe') === 'success' && filled($request->query('session_id'))) {
            $order = $stripe->completeCheckoutPayment((string) $request->query('session_id'));

            if ($order) {
                $request->session()->put('checkout.completed_order_id', $order->id);

                return redirect()
                    ->route('checkout.complete')
                    ->with('status', 'Stripe payment processed.');
            }

            return redirect()
                ->route('checkout.payment')
                ->withErrors([
                    'payment' => 'Stripe payment could not be confirmed.',
                ]);
        }

        if ($request->query('stripe') === 'cancelled') {
            return redirect()
                ->route('checkout.payment')
                ->with('status', 'Stripe checkout was cancelled.');
        }

        $pendingOrder = $this->pendingOrderFromSession($request);

        if ($pendingOrder && $pendingOrder->placed_at === null) {
            $checkout->reopenPendingOrder($pendingOrder);
            $request->session()->forget('checkout.pending_order_id');
        }

        if ($cart->items()->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('status', 'Your cart is empty.');
        }

        $shippingAddresses = $request->user()?->shippingAddresses()->get() ?? collect();
        $requestedAddressId = (int) $request->query('address', 0);

        if ($requestedAddressId > 0) {
            $sessionAddress = $shippingAddresses->firstWhere('id', $requestedAddressId);

            if ($sessionAddress) {
                $request->session()->put('checkout.selected_shipping_address_id', $sessionAddress->id);
            }

            return redirect()->route('checkout.index');
        }

        $selectedAddressId = (int) $request->session()->get('checkout.selected_shipping_address_id', 0);
        $selectedAddress = $shippingAddresses->firstWhere('id', $selectedAddressId)
            ?? $shippingAddresses->firstWhere('is_default', true)
            ?? $shippingAddresses->first();
        $usesSavedAddresses = $shippingAddresses->isNotEmpty() && $selectedAddress !== null;

        if ($selectedAddress) {
            $request->session()->put('checkout.selected_shipping_address_id', $selectedAddress->id);
        } else {
            $request->session()->forget('checkout.selected_shipping_address_id');
        }

        $addressDefaults = $selectedAddress ? [
            'customer_name' => $selectedAddress->recipient_name,
            'customer_email' => (string) optional($request->user())->email,
            'customer_phone' => (string) ($selectedAddress->phone ?? ''),
            'shipping_address_line_1' => $selectedAddress->address_line_1,
            'shipping_address_line_2' => (string) ($selectedAddress->address_line_2 ?? ''),
            'shipping_city' => $selectedAddress->city,
            'shipping_state' => (string) ($selectedAddress->state ?? ''),
            'shipping_postal_code' => $selectedAddress->postal_code,
            'shipping_country' => $selectedAddress->country,
        ] : [
            'customer_name' => (string) optional($request->user())->name,
            'customer_email' => (string) optional($request->user())->email,
            'customer_phone' => '',
            'shipping_address_line_1' => '',
            'shipping_address_line_2' => '',
            'shipping_city' => '',
            'shipping_state' => '',
            'shipping_postal_code' => '',
            'shipping_country' => 'BG',
        ];

        return view('checkout.form', [
            'items' => $cart->items(),
            'subtotalCents' => $cart->subtotalCents(),
            'discountSummary' => $discountCodes->summary($cart->subtotalCents()),
            'europeanCountries' => StoreCheckoutRequest::europeanCountries(),
            'checkoutDefaults' => $addressDefaults,
            'shippingAddresses' => $shippingAddresses,
            'selectedShippingAddress' => $selectedAddress,
            'usesSavedAddresses' => $usesSavedAddresses,
        ]);
    }

    /**
     * Display the unpaid payment step on its dedicated route.
     */
    public function payment(Request $request, StripePaymentService $stripe): View|RedirectResponse
    {
        if ($request->query('stripe') === 'success' && filled($request->query('session_id'))) {
            $order = $stripe->completeCheckoutPayment((string) $request->query('session_id'));

            if ($order) {
                $request->session()->put('checkout.completed_order_id', $order->id);

                return redirect()
                    ->route('checkout.complete')
                    ->with('status', 'Stripe payment processed.');
            }

            return redirect()
                ->route('checkout.payment')
                ->withErrors([
                    'payment' => 'Stripe payment could not be confirmed.',
                ]);
        }

        if ($request->query('stripe') === 'cancelled') {
            return redirect()
                ->route('checkout.payment')
                ->with('status', 'Stripe checkout was cancelled.');
        }

        $pendingOrder = $this->pendingOrderFromSession($request);

        if (! $pendingOrder || $pendingOrder->placed_at !== null) {
            return redirect()->route('checkout.index');
        }

        return view('checkout.confirmation', [
            'order' => $pendingOrder->load('items'),
        ]);
    }

    /**
     * Store the selected saved shipping address in the session.
     */
    public function selectAddress(Request $request): RedirectResponse
    {
        $user = $request->user();
        $selectedAddressId = (int) $request->input('address', 0);
        $selectedAddress = $user?->shippingAddresses()->find($selectedAddressId);

        if ($selectedAddress) {
            $request->session()->put('checkout.selected_shipping_address_id', $selectedAddress->id);
        } else {
            $request->session()->forget('checkout.selected_shipping_address_id');
        }

        return redirect()->route('checkout.index');
    }

    /**
     * Create an order from the current cart.
     */
    public function store(StoreCheckoutRequest $request, CheckoutService $checkout): RedirectResponse
    {
        $order = $checkout->placeOrder($request->validated(), $request->user());
        $request->session()->put('checkout.pending_order_id', $order->id);

        return redirect()->route('checkout.payment');
    }

    /**
     * Return from the payment step to address selection.
     */
    public function back(Request $request, CheckoutService $checkout): RedirectResponse
    {
        $pendingOrder = $this->pendingOrderFromSession($request);

        if ($pendingOrder && $pendingOrder->placed_at === null) {
            $checkout->reopenPendingOrder($pendingOrder);
            $request->session()->forget('checkout.pending_order_id');
        }

        return redirect()->route('checkout.index');
    }

    /**
     * Display the completed checkout confirmation page.
     */
    public function complete(Request $request, CartService $cart): View|RedirectResponse
    {
        $completedOrderId = (int) $request->session()->get('checkout.completed_order_id', 0);
        $order = $completedOrderId > 0 ? Order::query()->find($completedOrderId) : null;

        if (! $order || ! $this->canAccessOrder($request, $order) || $order->placed_at === null) {
            return redirect()->route('products.index');
        }

        if (
            $order->status === 'paid'
            && (int) $request->session()->get('checkout.pending_order_id', 0) === (int) $order->id
        ) {
            $cart->clear();
            $request->session()->forget('checkout.pending_order_id');
        }

        return view('checkout.confirmation', [
            'order' => $order->load('items'),
        ]);
    }

    /**
     * Download the completed checkout invoice as a PDF.
     */
    public function download(Request $request, ReceiptPdfService $pdfs): Response|RedirectResponse
    {
        $completedOrderId = (int) $request->session()->get('checkout.completed_order_id', 0);
        $order = $completedOrderId > 0 ? Order::query()->find($completedOrderId) : null;

        if (! $order || ! $this->canAccessOrder($request, $order) || $order->placed_at === null) {
            return redirect()->route('products.index');
        }

        $order->load(['items.product', 'payments']);
        $pdf = $pdfs->render($order);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="voidforge-receipt-VF'.$order->id.'.pdf"',
        ]);
    }

    /**
     * Redirect legacy order-specific checkout URLs away from public access.
     */
    public function show(Request $request, Order $order): RedirectResponse
    {
        if (! $this->canAccessOrder($request, $order)) {
            abort(404);
        }

        return redirect()->route('products.index');
    }

    /**
     * Resolve the current unpaid checkout from the session.
     */
    private function pendingOrderFromSession(Request $request): ?Order
    {
        $pendingOrderId = (int) $request->session()->get('checkout.pending_order_id', 0);

        if ($pendingOrderId <= 0) {
            return null;
        }

        $order = Order::query()->find($pendingOrderId);

        if (! $order || ! $this->canAccessOrder($request, $order)) {
            $request->session()->forget('checkout.pending_order_id');

            return null;
        }

        return $order;
    }

    /**
     * Determine whether the current visitor can access the order.
     */
    private function canAccessOrder(Request $request, Order $order): bool
    {
        if (
            $request->user()
            && $order->user_id !== null
            && $order->user_id !== $request->user()->id
        ) {
            return false;
        }

        if (
            ! $request->user()
            && $order->customer_email !== $request->session()->get('checkout.last_order_email')
        ) {
            return false;
        }

        return true;
    }
}
