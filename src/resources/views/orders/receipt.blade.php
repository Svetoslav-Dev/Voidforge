@extends('layouts.app', ['title' => ($order->placed_at ? 'Order #VF' : 'Pending Order #VF').$order->id.' | Voidforge'])

@section('content')
    <section class="card hero">
        <div class="receipt-header">
            <div>
                @if ($order->placed_at)
                    <p class="muted">Receipt</p>
                @endif
                <h1 style="color: #d89a58;">
                    @if ($order->placed_at)
                        Order
                    @else
                        <span style="color: #edf3ff;">Pending</span> Order
                    @endif
                    #VF{{ $order->id }}
                </h1>
                <p class="lead">
                    @if ($order->placed_at)
                        Purchased by {{ $order->customer_name }} on
                        {{ optional($order->placed_at)->format('F d, Y') ?? $order->created_at->format('F d, Y') }}.
                    @else
                        This order is waiting for payment confirmation. You can finish payment below.
                    @endif
                </p>
            </div>

            <div style="text-align: right;">
                @php($completedPayments = $order->payments->where('status', 'paid'))
                @if ($completedPayments->isNotEmpty())
                    @foreach ($completedPayments as $payment)
                        <h2 style="margin: 0; color: #4ecba3;">{{ ucfirst($payment->provider) }} · {{ strtoupper($payment->status) }}</h2>
                        <p class="muted" style="margin: 0.25rem 0 0;">{{ number_format($payment->amount / 100, 2) }} EUR</p>
                    @endforeach
                @else
                    <h2 style="margin: 0; color: #4ecba3;">
                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                    </h2>
                @endif
                <div class="actions" style="justify-content: flex-end; margin-top: 0.75rem;">
                    <a class="button secondary" href="{{ route('orders.index') }}">Back to receipt history</a>
                </div>
            </div>
        </div>
    </section>

    <section class="grid two" style="margin-top: 1.5rem;">
        <article class="card">
            <h2 style="color: #d89a58;">Shirts</h2>
            @foreach ($order->items as $item)
                <div class="receipt-item">
                    <div class="product-visual">
                        <img src="{{ $item->product?->image_url ?? asset('images/items/fallback.svg') }}" alt="{{ $item->product_name }}">
                    </div>

                    <p class="summary-line plain-line">
                        <span>{{ $item->product_name }} · {{ $item->product_size }} x {{ $item->quantity }}</span>
                        <strong>{{ number_format($item->line_total_cents / 100, 2) }} EUR</strong>
                    </p>
                </div>
            @endforeach

            <p class="summary-line plain-line">
                <span>Subtotal</span>
                <strong>{{ number_format($order->subtotal_cents / 100, 2) }} EUR</strong>
            </p>
            @if ($order->discount_cents > 0)
                <p class="summary-line plain-line receipt-discount-line">
                    <span>Discount · {{ $order->discount_code }}</span>
                    <strong>-{{ number_format($order->discount_cents / 100, 2) }} EUR</strong>
                </p>
            @endif
            <p class="summary-line total-line">
                <span>Total</span>
                <strong>{{ number_format($order->total_cents / 100, 2) }} EUR</strong>
            </p>
        </article>

        <article class="card">
            <h2 style="color: #d89a58;">Receipt Details</h2>
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

            @if ($order->status !== 'paid')
                <div class="actions" style="margin-top: 0.75rem;">
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

        </article>
    </section>
@endsection
