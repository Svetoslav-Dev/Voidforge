@extends('layouts.app', ['title' => $product->name.' | Voidforge'])

@section('content')
    @php($shirtSizes = \App\Models\Product::shirtSizes())
    @php($defaultSize = old('size', $product->defaultShirtSize()))
    @php($hasPurchasableSize = ! empty($product->availableShirtSizes()))
    @php($shirtImages = [
        ['label' => 'Front', 'url' => $product->front_image_url],
        ['label' => 'Back', 'url' => $product->back_image_url],
    ])
    <section class="card product-show" data-product-gallery>
        <div>
            <p class="muted">{{ $product->category?->name ?? 'Uncategorized' }}</p>
            <h1>{{ $product->name }}</h1>
            <p class="lead">{{ $product->description }}</p>
        </div>

        <div class="product-show-top product-show-panels">
            <div class="product-gallery">
                <div
                    class="product-visual product-show-visual product-gallery-main"
                    data-gallery-frame
                    >
                        <img
                            src="{{ $shirtImages[0]['url'] }}"
                            alt="{{ $product->name }} {{ strtolower($shirtImages[0]['label']) }}"
                            data-gallery-main
                        >
                    </div>
            </div>

            <div class="card">
                <h2>Shirt Details</h2>
                <p class="muted">SKU: {{ $product->sku }}</p>
                <p class="muted">Price: {{ number_format($product->price_cents / 100, 2) }} EUR</p>
                <p class="muted">
                    Availability:
                    <span class="stock-pill {{ $product->stock <= 0 ? 'empty' : ($product->stock <= 5 ? 'limited' : '') }}">
                        {{ $product->stock_label }}
                    </span>
                </p>

                @if ($errors->any())
                    <div class="errors">
                        <strong>Could not update cart.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($product->stock > 0 && $hasPurchasableSize)
                    <form method="POST" action="{{ route('cart.store', $product) }}">
                        @csrf
                        <div class="field">
                            <label for="size">Size</label>
                            <select id="size" name="size" required>
                                @foreach ($shirtSizes as $size)
                                    <option
                                        value="{{ $size }}"
                                        @selected($defaultSize === $size)
                                        @disabled(! $product->isShirtSizeAvailable($size))
                                    >
                                        {{ $size }}{{ $product->isShirtSizeAvailable($size) ? '' : ' · Sold out' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('size')<p class="muted">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="quantity">Quantity</label>
                            <input id="quantity" name="quantity" type="number" min="1" max="{{ min(99, $product->stock) }}" value="1" required>
                        </div>
                        <button type="submit">Add to cart</button>
                    </form>
                @elseif ($product->stock > 0)
                    <p class="muted">All shirt sizes are currently sold out for this design.</p>
                @endif
            </div>
        </div>

        <div class="product-show-bottom product-show-panels">
            <div class="product-gallery-thumbs">
                @foreach ($shirtImages as $index => $image)
                    <button
                        type="button"
                        class="product-visual product-gallery-thumb {{ $index === 0 ? 'is-active' : '' }}"
                        data-gallery-thumb
                        data-image-url="{{ $image['url'] }}"
                        data-image-alt="{{ $product->name }} {{ strtolower($image['label']) }}"
                        aria-label="Show {{ strtolower($image['label']) }} of {{ $product->name }}"
                    >
                        <img src="{{ $image['url'] }}" alt="{{ $product->name }} {{ strtolower($image['label']) }}">
                    </button>
                @endforeach
            </div>

            <div class="card">
                <h2>Shirt Page</h2>
                <p class="muted">Pick your size now, or return later through your account purchase history and receipts.</p>
                <div class="actions">
                    <a class="button secondary" href="{{ route('products.index') }}">Back to shirts</a>
                    @if (($cartItemCount ?? 0) > 0)
                        <a class="button" href="{{ route('cart.index') }}">Open cart</a>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
