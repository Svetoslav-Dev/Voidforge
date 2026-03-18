@extends('layouts.app', ['title' => 'Cart | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <p class="muted">Shopping Cart</p>
            <h1>Review your shirt selection.</h1>
            <p class="lead">
                This cart is stored in the session for now. Checkout comes next, so this page focuses on
                quantity control and order subtotal.
            </p>
        </div>
    </section>

    @if ($items->isEmpty())
        <section class="card empty-state">
            <h2>Your cart is empty</h2>
            <p class="muted">Browse the shirts and add a few pieces before moving to checkout.</p>
            <div class="actions">
                <a class="button" href="{{ route('products.index') }}">Browse shirts</a>
            </div>
        </section>
    @else
        <section class="cart-layout">
            @php($firstItem = $items->first())
            @php($remainingItems = $items->slice(1))

            <article class="card cart-item">
                <div class="cart-item-main">
                    <div class="product-visual cart-item-visual">
                        <img src="{{ $firstItem['product']->image_url }}" alt="{{ $firstItem['product']->name }}">
                    </div>

                    <div>
                        <p class="muted">{{ $firstItem['product']->category?->name ?? 'Uncategorized' }}</p>
                        <h2>{{ $firstItem['product']->name }}</h2>
                        <p class="muted">SKU: {{ $firstItem['product']->sku }}</p>
                        <p class="muted">Size: {{ $firstItem['size'] }}</p>
                        <p class="cart-item-price">{{ number_format($firstItem['product']->price_cents / 100, 2) }} EUR each</p>
                    </div>
                </div>

                <div class="cart-item-actions">
                    <form class="inline-form" method="POST" action="{{ route('cart.update', $firstItem['product']) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="size" value="{{ $firstItem['size'] }}">
                        <label for="quantity-{{ $firstItem['key'] }}">Qty</label>
                        <input
                            id="quantity-{{ $firstItem['key'] }}"
                            name="quantity"
                            type="number"
                            min="0"
                            max="{{ min(99, $firstItem['product']->stock) }}"
                            value="{{ $firstItem['quantity'] }}"
                        >
                        <button type="submit">Update</button>
                    </form>

                    <form method="POST" action="{{ route('cart.destroy', $firstItem['product']) }}">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="size" value="{{ $firstItem['size'] }}">
                        <button class="button secondary" type="submit">Remove</button>
                    </form>
                </div>

                <div>
                    <strong>{{ number_format($firstItem['line_total_cents'] / 100, 2) }} EUR</strong>
                </div>
            </article>

            <aside class="card summary-card">
                <p class="muted">Order Summary</p>
                <h2>{{ $itemCount }} shirt{{ $itemCount === 1 ? '' : 's' }}</h2>
                <p class="summary-line">
                    <span>Subtotal</span>
                    <strong>{{ number_format($subtotalCents / 100, 2) }} EUR</strong>
                </p>
                <p class="muted">Shipping and payment are added in the next checkout step.</p>
                <div class="actions">
                    <a class="button" href="{{ route('checkout.index') }}">Checkout</a>
                    <a class="button secondary" href="{{ route('products.index') }}">Continue shopping</a>
                </div>
            </aside>

            @if ($remainingItems->isNotEmpty())
                <div class="cart-items cart-items-rest">
                    @foreach ($remainingItems as $item)
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

                            <div>
                                <strong>{{ number_format($item['line_total_cents'] / 100, 2) }} EUR</strong>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    @endif
@endsection
