@extends('layouts.app', ['title' => $order->placed_at ? __('orders.receipt_page_title_placed', ['id' => $order->id]) : __('orders.receipt_page_title_pending', ['id' => $order->id])])

@section('content')
    <section class="card hero">
        <div class="receipt-header">
            <div>
                @if ($order->placed_at)
                    <p class="muted">{{ __('orders.receipt_label') }}</p>
                @endif
                <h1 style="color: #d89a58;">
                    @if ($order->placed_at)
                        {{ __('orders.receipt_order') }}
                    @else
                        <span style="color: #edf3ff;">{{ __('orders.receipt_pending') }}</span> {{ __('orders.receipt_order') }}
                    @endif
                    #VF{{ $order->id }}
                </h1>
                <p class="lead">
                    @if ($order->placed_at)
                        {{ __('orders.receipt_purchased_by', ['name' => $order->customer_name, 'date' => optional($order->placed_at)->format('F d, Y') ?? $order->created_at->format('F d, Y')]) }}
                    @else
                        {{ __('orders.receipt_awaiting_payment') }}
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
                    <a class="button secondary" href="{{ route('orders.index') }}">{{ __('orders.back_to_history') }}</a>
                </div>
            </div>
        </div>
    </section>

    <section class="grid two" style="margin-top: 1.5rem;">
        <article class="card">
            <h2 style="color: #d89a58;">{{ __('orders.shirts_section') }}</h2>
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
                <span>{{ __('orders.subtotal') }}</span>
                <strong>{{ number_format($order->subtotal_cents / 100, 2) }} EUR</strong>
            </p>
            @if ($order->discount_cents > 0)
                <p class="summary-line plain-line receipt-discount-line">
                    <span>{{ __('orders.discount_line', ['code' => $order->discount_code]) }}</span>
                    <strong>-{{ number_format($order->discount_cents / 100, 2) }} EUR</strong>
                </p>
            @endif
            <p class="summary-line total-line">
                <span>{{ __('orders.total') }}</span>
                <strong>{{ number_format($order->total_cents / 100, 2) }} EUR</strong>
            </p>
        </article>

        <article class="card">
            <h2 style="color: #d89a58;">{{ __('orders.receipt_details') }}</h2>
            <p><strong>{{ __('orders.label_name') }}</strong> <span class="muted">{{ $order->customer_name }}</span></p>
            <p><strong>{{ __('orders.label_email') }}</strong> <span class="muted">{{ $order->customer_email }}</span></p>
            @if ($order->customer_phone)
                <p><strong>{{ __('orders.label_phone') }}</strong> <span class="muted">{{ $order->customer_phone }}</span></p>
            @endif
            <p>
                <strong>{{ __('orders.label_address') }}</strong>
                <span class="muted">
                    {{ $order->shipping_address_line_1 }}
                    @if ($order->shipping_address_line_2)
                        , {{ $order->shipping_address_line_2 }}
                    @endif
                </span>
            </p>
            <p><strong>{{ __('orders.label_city') }}</strong> <span class="muted">{{ $order->shipping_city }}</span></p>
            @if ($order->shipping_state)
                <p><strong>{{ __('orders.label_state') }}</strong> <span class="muted">{{ $order->shipping_state }}</span></p>
            @endif
            <p><strong>{{ __('orders.label_postal_code') }}</strong> <span class="muted">{{ $order->shipping_postal_code }}</span></p>
            <p><strong>{{ __('orders.label_country') }}</strong> <span class="muted">{{ $order->shipping_country }}</span></p>

            @if ($order->status !== 'paid')
                <div class="actions" style="margin-top: 0.75rem;">
                    <form method="POST" action="{{ route('stripe.checkout.store', $order) }}">
                        @csrf
                        <button type="submit">{{ __('orders.pay_stripe') }}</button>
                    </form>

                    <form method="POST" action="{{ route('paypal.checkout.store', $order) }}">
                        @csrf
                        <button type="submit">{{ __('orders.pay_paypal') }}</button>
                    </form>
                </div>
            @endif

        </article>
    </section>
@endsection
