@extends('layouts.app', ['title' => $product->name.' | Voidforge'])

@section('content')
    @php($shirtSizes = \App\Models\Product::shirtSizes())
    @php($defaultSize = old('size', $product->defaultShirtSize()))
    @php($hasPurchasableSize = ! empty($product->availableShirtSizes()))
    @php($shirtImages = [
        ['label' => __('products.details_front'), 'url' => $product->front_image_url],
        ['label' => __('products.details_back'), 'url' => $product->back_image_url],
    ])
    <section class="card product-show" data-product-gallery>
        <div>
            <p class="muted">{{ $product->category?->name ?? __('products.uncategorized') }}</p>
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
                <h2>{{ __('products.details_shirt_details') }}</h2>
                <p class="muted">{{ __('products.details_sku', ['sku' => $product->sku]) }}</p>
                <p class="muted">{{ __('products.details_price', ['price' => number_format($product->price_cents / 100, 2)]) }}</p>
                <p class="muted">
                    {{ __('products.details_availability') }}
                    <span class="stock-pill {{ $product->stock <= 0 ? 'empty' : ($product->stock <= 5 ? 'limited' : '') }}">
                        {{ $product->stock_label }}
                    </span>
                </p>

                @if ($errors->any())
                    <div class="errors">
                        <strong>{{ __('products.details_could_not_update_cart') }}</strong>
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
                            <label for="size">{{ __('products.details_size') }}</label>
                            <select id="size" name="size" required>
                                @foreach ($shirtSizes as $size)
                                    <option
                                        value="{{ $size }}"
                                        @selected($defaultSize === $size)
                                        @disabled(! $product->isShirtSizeAvailable($size))
                                    >
                                        {{ $product->isShirtSizeAvailable($size) ? $size : __('products.details_sold_out_size', ['size' => $size]) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('size')<p class="muted">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="quantity">{{ __('products.details_quantity') }}</label>
                            <select id="quantity" name="quantity" required>
                                @foreach (range(1, min(10, $product->stock)) as $quantity)
                                    <option value="{{ $quantity }}" @selected(old('quantity', 1) == $quantity)>{{ $quantity }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit">{{ __('products.add_to_cart') }}</button>
                    </form>
                @elseif ($product->stock > 0)
                    <p class="muted">{{ __('products.details_all_sizes_sold_out') }}</p>
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
                <h2>{{ __('products.details_shirt_page') }}</h2>
                <p class="muted">{{ __('products.details_shirt_page_hint') }}</p>
                <div class="actions">
                    <a class="button secondary" href="{{ route('products.index') }}">{{ __('products.details_back_to_shirts') }}</a>
                    @if (($cartItemCount ?? 0) > 0)
                        <a class="button" href="{{ route('cart.index') }}">{{ __('products.details_open_cart') }}</a>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
