@extends('layouts.app', ['title' => 'Track Your Order | VoidForgeStore'])

@section('content')
    <section class="card hero">
        <div>
            <h1>Track your order</h1>
            <p class="lead">Enter the email address used at checkout and your order reference to see the current status of your order.</p>
        </div>
    </section>

    <section class="card" style="margin-top: 1.5rem;">
        <form method="POST" action="{{ route('order.tracking.search') }}" data-order-tracking-form>
            @csrf

            <div class="grid two">
                <div class="field">
                    <label for="tracking_email">Email address</label>
                    <input id="tracking_email" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="email">
                    @error('email')<p class="muted">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="tracking_reference">Order reference</label>
                    <input id="tracking_reference" name="order_reference" type="text" value="{{ old('order_reference') }}" placeholder="VF12" required>
                    @error('order_reference')<p class="muted">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="actions">
                <button type="submit">Track order</button>
            </div>
        </form>
    </section>

    <div data-order-tracking-result>
        @if ($searched)
            @if ($order)
                <section class="card" style="margin-top: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.25rem;">
                        <h2 style="margin: 0; color: #d89a58;">Order #VF{{ $order->id }}</h2>
                        <span class="stock-pill {{ $order->status === 'paid' ? '' : 'limited' }}">
                            {{ ucwords(str_replace('_', ' ', $order->status)) }}
                        </span>
                    </div>

                    <div class="grid two">
                        <div>
                            <p class="muted" style="margin: 0 0 0.25rem;">Placed</p>
                            <p style="margin: 0;">{{ $order->placed_at->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="muted" style="margin: 0 0 0.25rem;">Ships to</p>
                            <p style="margin: 0;">{{ $order->shipping_city }}, {{ $order->shipping_country }}</p>
                        </div>
                        @if ($order->payments->first()?->provider)
                            <div>
                                <p class="muted" style="margin: 0 0 0.25rem;">Payment</p>
                                <p style="margin: 0;">{{ ucfirst($order->payments->first()->provider) }}</p>
                            </div>
                        @endif
                        <div>
                            <p class="muted" style="margin: 0 0 0.25rem;">Total</p>
                            <p style="margin: 0; font-weight: 700;">{{ number_format($order->total_cents / 100, 2) }} EUR</p>
                        </div>
                    </div>

                    <div style="margin-top: 1.25rem; border-top: 1px solid var(--line); padding-top: 1.25rem;">
                        @foreach ($order->items as $item)
                            <p class="summary-line plain-line">
                                <span>{{ $item->product_name }} · {{ $item->product_size }} × {{ $item->quantity }}</span>
                                <strong>{{ number_format($item->line_total_cents / 100, 2) }} EUR</strong>
                            </p>
                        @endforeach
                    </div>

                    @auth
                        @if (auth()->user()->email === $order->customer_email || auth()->user()->is_admin)
                            <div class="actions" style="margin-top: 1rem;">
                                <a class="button secondary" href="{{ route('orders.show', ['orderReference' => 'VF'.$order->id]) }}">Open full receipt</a>
                            </div>
                        @endif
                    @endauth
                </section>
            @else
                <section class="card" style="margin-top: 1.5rem;">
                    <h2>No order found</h2>
                    <p class="muted">No order matched that email and reference. Check that the email matches the one used at checkout and that the reference is correct, for example VF12.</p>
                </section>
            @endif
        @endif
    </div>
@endsection
