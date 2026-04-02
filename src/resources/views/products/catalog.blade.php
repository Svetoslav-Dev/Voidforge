@extends('layouts.app', ['title' => __('products.page_title')])

@section('content')
    <section class="card hero">
        <div>
            <h1 style="color: #d89a58;">{{ __('products.heading') }}</h1>
            <p class="lead">{{ __('products.lead') }}</p>
        </div>

        <div data-product-catalog>
            <div class="chip-row" style="margin-top: 0.8rem;" data-product-catalog-chips>
                <a class="chip {{ $selectedCategory ? '' : 'active' }}" href="{{ route('products.index') }}">{{ __('products.category_all') }}</a>
                @foreach ($categories as $category)
                    <a
                        class="chip {{ $selectedCategory === $category->slug ? 'active' : '' }}"
                        href="{{ route('products.index', ['category' => $category->slug]) }}"
                    >
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>

            <div data-product-catalog-results>
                <section class="product-grid">
                    @forelse ($products as $product)
                        <article class="card product-card">
                            <div class="product-visual">
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                            </div>

                            <div class="product-meta">
                                <p class="muted">{{ $product->category?->name ?? __('products.uncategorized') }}</p>
                                <h2>{{ $product->name }}</h2>
                                <p class="muted">{{ $product->description }}</p>
                            </div>

                            <div class="product-foot">
                                <div class="product-foot-meta">
                                    <div class="product-foot-row">
                                        <strong>{{ number_format($product->price_cents / 100, 2) }} EUR</strong>
                                        <p class="muted">
                                            <span class="stock-pill {{ $product->stock <= 0 ? 'empty' : ($product->stock <= 5 ? 'limited' : '') }}">
                                                {{ $product->stock_label }}
                                            </span>
                                        </p>
                                    </div>

                                    <div class="actions product-card-actions">
                                        <a class="button secondary" href="{{ route('products.show', $product) }}">{{ __('products.view_shirt') }}</a>

                                        @if ($product->stock > 0 && $product->defaultShirtSize())
                                            <form method="POST" action="{{ route('cart.store', $product) }}" data-ajax-add-to-cart>
                                                @csrf
                                                <input type="hidden" name="quantity" value="1">
                                                <input type="hidden" name="size" value="{{ $product->defaultShirtSize() }}">
                                                <button type="submit">{{ __('products.add_to_cart') }}</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="card">
                            <h2>{{ __('products.no_shirts_found') }}</h2>
                            <p class="muted">{{ __('products.no_shirts_found_hint') }}</p>
                        </div>
                    @endforelse
                </section>

                @if ($products->hasPages())
                    <div class="pagination-wrap">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
