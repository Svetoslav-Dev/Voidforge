@extends('layouts.app', ['title' => 'Manage Shirts | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <p class="muted">Admin Shirts</p>
            <h1>Manage the storefront lineup.</h1>
            <p class="lead">Create, edit, and archive shirts without touching public routes directly.</p>
        </div>

        <div class="admin-toolbar">
            <div class="admin-toolbar-left">
                <a class="button" href="{{ route('admin.products.create') }}">Add shirt</a>
                <a class="button secondary" href="{{ route('admin.panel') }}">Back to dashboard</a>
            </div>

            <form method="GET" action="{{ route('admin.products.index') }}" class="inline-form admin-toolbar-right admin-search">
                <input type="text" name="q" value="{{ $search }}" placeholder="Search shirts">
                <button class="button secondary" type="submit">Search</button>
            </form>
        </div>
    </section>

    <section class="grid">
        @foreach ($products as $product)
            <article class="card">
                <div class="admin-product-main">
                    <div>
                        <p class="muted">{{ $product->category?->name ?? 'Uncategorized' }}</p>
                        <h2>{{ $product->name }}</h2>
                        <p class="muted">SKU: {{ $product->sku }}</p>
                        <p class="muted">
                            Status:
                            @if ($product->trashed())
                                Archived
                            @elseif ($product->is_active)
                                Active
                            @else
                                Draft
                            @endif
                        </p>
                        <div class="actions">
                            @if (! $product->trashed())
                                <a class="button secondary" href="{{ route('admin.products.edit', $product) }}">Edit</a>

                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Archive</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.products.restore', $product->slug) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit">Restore</button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="admin-product-images">
                        <div>
                            <p class="muted" style="margin: 0 0 0.35rem;">Front</p>
                            <div class="product-visual admin-product-visual">
                                <img src="{{ $product->front_image_url }}" alt="{{ $product->name }} front">
                            </div>
                        </div>

                        <div>
                            <p class="muted" style="margin: 0 0 0.35rem;">Back</p>
                            <div class="product-visual admin-product-visual">
                                <img src="{{ $product->back_image_url }}" alt="{{ $product->name }} back">
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    @if ($products->hasPages())
        <div class="pagination-wrap">
            {{ $products->links() }}
        </div>
    @endif
@endsection
