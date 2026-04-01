@extends('layouts.app', ['title' => 'Order #VF'.$order->id.' | Admin'])

@section('content')
    <section class="card hero">
        <div>
            <h1 style="color: #d89a58;">Order #VF{{ $order->id }}</h1>
            <p class="lead">Status: {{ ucfirst(str_replace('_', ' ', $order->status)) }}</p>
        </div>
    </section>

    <section class="grid two">
        <article class="card">
            <h2 style="color: #4ecba3;">Customer</h2>
            <p>{{ $order->customer_name }}</p>
            <p class="muted">{{ $order->customer_email }}</p>
            @if ($order->customer_phone)
                <p class="muted">{{ $order->customer_phone }}</p>
            @endif
        </article>

        <article class="card">
            <h2 style="color: #4ecba3;">Totals</h2>
            <p class="muted">Subtotal: {{ number_format($order->subtotal_cents / 100, 2) }} EUR</p>
            <p class="muted">Shipping: {{ number_format($order->shipping_cents / 100, 2) }} EUR</p>
            @if ($order->discount_cents > 0)
                <p class="muted">Discount: -{{ number_format($order->discount_cents / 100, 2) }} EUR ({{ $order->discount_code }})</p>
            @endif
            <p><strong>Total: {{ number_format($order->total_cents / 100, 2) }} EUR</strong></p>
        </article>
    </section>

    <section class="grid two">
        <article class="card">
            <h2 style="color: #4ecba3;">Shipping Address</h2>
            <p>{{ $order->shipping_address_line_1 }}</p>
            @if ($order->shipping_address_line_2)
                <p class="muted">{{ $order->shipping_address_line_2 }}</p>
            @endif
            <p class="muted">{{ $order->shipping_city }}@if ($order->shipping_state), {{ $order->shipping_state }}@endif {{ $order->shipping_postal_code }}</p>
            <p class="muted">{{ $order->shipping_country }}</p>
        </article>

        <article class="card">
            <h2 style="color: #4ecba3;">Email Receipt</h2>
            @if ($order->email_status === \App\Models\Order::EMAIL_STATUS_SENT)
                <p>Sent</p>
                @if ($order->receipt_emailed_at)
                    <p class="muted">{{ $order->receipt_emailed_at->format('M d, Y H:i') }}</p>
                @endif
            @elseif ($order->email_status === \App\Models\Order::EMAIL_STATUS_PENDING)
                <p class="muted">Pending delivery</p>
            @elseif ($order->email_status === \App\Models\Order::EMAIL_STATUS_FAILED)
                <p style="color: #e05252;">Failed — run <code>orders:retry-completed-emails</code> to retry</p>
            @else
                <p class="muted">Not yet queued</p>
            @endif
        </article>
    </section>

    <section class="grid two">
        <article class="card">
            <h2 style="color: #4ecba3;">Shirts</h2>
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
        </article>

        <article class="card">
            <h2 style="color: #4ecba3;">Payments</h2>
            @forelse ($order->payments as $payment)
                <p class="summary-line plain-line">
                    <span>{{ ucfirst($payment->provider) }} · {{ $payment->status }}</span>
                    <strong>{{ number_format($payment->amount / 100, 2) }} EUR</strong>
                </p>
            @empty
                <p class="muted">No payment records yet.</p>
            @endforelse
        </article>
    </section>
@endsection
