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
                <p class="muted">Shipping and payment are added in the next checkout step.</p>
                <div class="actions cart-summary-actions">
                    <a class="button cart-checkout-button" href="{{ route('checkout.index') }}">Confirm shipping address</a>
                </div>
            </aside>
        </section>
    @endif
@endsection
