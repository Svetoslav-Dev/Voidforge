@extends('layouts.app', ['title' => 'Cart | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <h1>Review your shirt selection.</h1>
            <p class="lead">
                This cart is stored in the session for now. Checkout comes next, so this page focuses on
                quantity control and order subtotal.
            </p>
        </div>
    </section>

    @if ($items->isEmpty())
        <section class="card empty-state cart-empty-state">
            <h2>Your cart is empty</h2>
            <p class="muted">Browse the shirts and add a few pieces before moving to checkout.</p>
        </section>
    @else
        <section class="cart-layout">
            <div class="cart-items">
                @foreach ($items as $item)
                    <article class="card cart-item">
                        <div class="cart-item-main">
                            <div class="product-visual cart-item-visual">
                                <img src="{{ $item['product']->image_url }}" alt="{{ $item['product']->name }}">
                            </div>

                            <div>
                                <p class="muted">{{ $item['product']->category?->name ?? 'Uncategorized' }}</p>
                                <h2>{{ $item['product']->name }}</h2>
                                <p class="muted">SKU: {{ $item['product']->sku }}</p>
                                <p class="muted">Size: {{ $item['size'] }}</p>
                                <p class="cart-item-price">{{ number_format($item['product']->price_cents / 100, 2) }} EUR each</p>
                            </div>
                        </div>

                        <div class="cart-item-actions">
                            <form class="inline-form" method="POST" action="{{ route('cart.update', $item['product']) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="size" value="{{ $item['size'] }}">
                                <label for="quantity-{{ $item['key'] }}">Qty</label>
                                <input
                                    id="quantity-{{ $item['key'] }}"
                                    name="quantity"
                                    type="number"
                                    min="0"
                                    max="{{ min(99, $item['product']->stock) }}"
                                    value="{{ $item['quantity'] }}"
                                >
                                <button type="submit">Update</button>
                            </form>

                            <form method="POST" action="{{ route('cart.destroy', $item['product']) }}">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="size" value="{{ $item['size'] }}">
                                <button class="button secondary" type="submit">Remove</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <aside class="card summary-card cart-summary-card">
                <p class="cart-order-summary-title">Order Summary</p>
                <form method="POST" action="{{ route('cart.discount.store') }}">
                    @csrf
                    <div class="field" style="margin-bottom: 0.5rem;">
                        <label for="discount_code">Discount code</label>
                        <div style="display:flex; gap:0.5rem; align-items:center;">
                            <input id="discount_code" name="code" type="text" value="{{ old('code', $discountSummary['code'] ?? '') }}" placeholder="WELCOME10">
                            <button type="submit">Apply</button>
                        </div>
                        @error('code')<p class="muted">{{ $message }}</p>@enderror
                    </div>
                </form>

                @if ($discountSummary)
                    <div class="actions" style="margin-top: 0;">
                        <p class="muted" style="margin:0;">Applied code {{ $discountSummary['code'] }}</p>
                        <form method="POST" action="{{ route('cart.discount.destroy') }}">
                            @csrf
                            @method('DELETE')
                            <button class="button secondary" type="submit">Remove</button>
                        </form>
                    </div>
                @endif

                @php($summaryItems = $items->groupBy(fn ($item) => $item['product']->id)->map(function ($group) {
                    return [
                        'name' => $group->first()['product']->name,
                        'quantity' => $group->sum('quantity'),
                        'line_total_cents' => $group->sum('line_total_cents'),
                    ];
                }))
                @foreach ($summaryItems as $item)
                    <p class="summary-line plain-line">
                        <span>{{ $item['name'] }} x {{ $item['quantity'] }}</span>
                        <strong>{{ number_format($item['line_total_cents'] / 100, 2) }} EUR</strong>
                    </p>
                @endforeach
                <p class="summary-line">
                    <span>Subtotal</span>
                    <strong>{{ number_format($subtotalCents / 100, 2) }} EUR</strong>
                </p>
                @if ($discountSummary)
                    <p class="summary-line plain-line">
                        <span>Discount · {{ $discountSummary['code'] }}</span>
                        <strong>-{{ number_format($discountSummary['discount_cents'] / 100, 2) }} EUR</strong>
                    </p>
                    <p class="summary-line total-line">
                        <span>Total</span>
                        <strong>{{ number_format($discountSummary['total_cents'] / 100, 2) }} EUR</strong>
                    </p>
                @endif
                <p class="muted">Shipping and payment are added in the next checkout step.</p>
                <div class="actions cart-summary-actions">
                    <a class="button cart-checkout-button" href="{{ route('checkout.index') }}">Confirm shipping address</a>
                </div>
            </aside>
        </section>
    @endif
@endsection
