@extends('layouts.app', ['title' => 'Shirts | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <h1 style="color: #d89a58;">Built for a darker lineup of shirts</h1>
            <p class="lead">
                Browse the current shirts by category. The demo lineup now carries more shirts so the
                storefront feels closer to a real first release.
            </p>
        </div>

        <div data-product-catalog>
            <div class="chip-row" style="margin-top: 0.8rem;" data-product-catalog-chips>
                <a class="chip {{ $selectedCategory ? '' : 'active' }}" href="{{ route('products.index') }}">All</a>
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
                                <p class="muted">{{ $product->category?->name ?? 'Uncategorized' }}</p>
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
                                        <a class="button secondary" href="{{ route('products.show', $product) }}">View shirt</a>

                                        @if ($product->stock > 0 && $product->defaultShirtSize())
                                            <form method="POST" action="{{ route('cart.store', $product) }}" data-ajax-add-to-cart>
                                                @csrf
                                                <input type="hidden" name="quantity" value="1">
                                                <input type="hidden" name="size" value="{{ $product->defaultShirtSize() }}">
                                                <button type="submit">Add to cart</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="card">
                            <h2>No shirts found</h2>
                            <p class="muted">Try another category or seed the demo shirts again.</p>
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
