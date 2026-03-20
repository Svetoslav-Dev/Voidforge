@extends('layouts.app', ['title' => 'Order '.($order->placed_at ? 'Completed' : 'Payment Step').' | Voidforge'])

@section('content')
    @php($paidPayment = $order->payments->where('status', 'paid')->sortByDesc('id')->first())
    <section class="card hero">
        <div>
            <div class="checkout-complete-heading">
            <h1>
                @if ($order->placed_at)
                    Order #VF{{ $order->id }} is {{ str_replace('_', ' ', $order->status) }}
                @else
                    Checkout is {{ str_replace('_', ' ', $order->status) }}
                @endif
            </h1>
            @if ($order->placed_at)
                <a class="button secondary" href="{{ route('checkout.download') }}">Download invoice</a>
            @endif
            </div>
            <p class="lead">
                @if ($order->status === 'paid')
                    Your order has been placed and payment was confirmed.
                    A payment provider has confirmed the payment for this order.
                @elseif ($order->placed_at === null)
                    Your order has not been placed yet.
                    It will only be placed after Stripe or PayPal confirms the payment.
                @else
                    The order has been created and is waiting for payment confirmation.
                @endif
                @if ($order->status === 'paid')
                    A payment provider has confirmed the payment for this order.
                @endif
            </p>
        </div>
    </section>

    @error('payment')
        <div class="errors">
            <strong>Payment could not be started.</strong>
            <p>{{ $message }}</p>
        </div>
    @enderror

    <section class="grid two checkout-confirmation-grid" data-checkout-complete-cards>
        <article class="card checkout-shipment-card" data-checkout-shipment-card>
            <h2>Shipment details</h2>
            <p><strong>Name:</strong> <span class="muted">{{ $order->customer_name }}</span></p>
            <p><strong>Email:</strong> <span class="muted">{{ $order->customer_email }}</span></p>
            @if ($order->customer_phone)
                <p><strong>Phone:</strong> <span class="muted">{{ $order->customer_phone }}</span></p>
            @endif
            <p>
                <strong>Address:</strong>
                <span class="muted">
                    {{ $order->shipping_address_line_1 }}
                    @if ($order->shipping_address_line_2)
                        , {{ $order->shipping_address_line_2 }}
                    @endif
                </span>
            </p>
            <p><strong>City:</strong> <span class="muted">{{ $order->shipping_city }}</span></p>
            @if ($order->shipping_state)
                <p><strong>State:</strong> <span class="muted">{{ $order->shipping_state }}</span></p>
            @endif
            <p><strong>Postal code:</strong> <span class="muted">{{ $order->shipping_postal_code }}</span></p>
            <p><strong>Country:</strong> <span class="muted">{{ $order->shipping_country }}</span></p>
        </article>

        <article class="card checkout-summary-card" data-checkout-summary-card>
            <h2>Summary</h2>
            @foreach ($order->items as $item)
                <p class="summary-line plain-line">
                    <span>{{ $item->product_name }} · {{ $item->product_size }} x {{ $item->quantity }}</span>
                    <strong>{{ number_format($item->line_total_cents / 100, 2) }} EUR</strong>
                </p>
            @endforeach
            <p class="summary-line plain-line">
                <span>Subtotal</span>
                <strong>{{ number_format($order->subtotal_cents / 100, 2) }} EUR</strong>
            </p>
            @if ($order->discount_cents > 0)
                <p class="summary-line plain-line">
                    <span>Discount · {{ $order->discount_code }}</span>
                    <strong>-{{ number_format($order->discount_cents / 100, 2) }} EUR</strong>
                </p>
            @endif
            <p class="summary-line plain-line">
                <span>Status</span>
                <strong>{{ ucwords(str_replace('_', ' ', $order->status)) }}</strong>
            </p>
            @if ($paidPayment)
                <p class="summary-line plain-line">
                    <span>Payment method</span>
                    <strong>{{ ucfirst($paidPayment->provider) }}</strong>
                </p>
            @endif
            <p class="summary-line total-line">
                <span>Total</span>
                <strong>{{ number_format($order->total_cents / 100, 2) }} EUR</strong>
            </p>

            @if ($order->status !== 'paid')
                <div class="actions checkout-back-actions">
                    <form method="POST" action="{{ route('checkout.back') }}">
                        @csrf
                        <button type="submit" class="button secondary">Back to address selection</button>
                    </form>
                </div>

                <div class="actions checkout-payment-actions">
                    <form method="POST" action="{{ route('stripe.checkout.store', $order) }}">
                        @csrf
                        <button type="submit">Pay with Stripe</button>
                    </form>

                    <form method="POST" action="{{ route('paypal.checkout.store', $order) }}">
                        @csrf
                        <button type="submit">Pay with PayPal</button>
                    </form>
                </div>
            @endif

            @if ($order->placed_at)
                <div class="actions checkout-complete-actions">
                    @auth
                        <a class="button secondary" href="{{ route('orders.index') }}">Purchase history</a>
                    @endauth
                </div>
            @endif
        </article>
    </section>
@endsection
