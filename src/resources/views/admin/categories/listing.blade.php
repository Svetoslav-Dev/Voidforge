@extends('layouts.app', ['title' => 'Catalogs | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <p class="muted">Admin Catalogs</p>
            <h1>Manage the storefront catalogs.</h1>
            <p class="lead">Create, edit, and archive catalogs without removing their history from the database.</p>
        </div>

        <div class="admin-toolbar">
            <div class="admin-toolbar-left">
                <a class="button" href="{{ route('admin.categories.create') }}">Add catalog</a>
                <a class="button secondary" href="{{ route('admin.panel') }}">Back to admin</a>
            </div>

            <form method="GET" action="{{ route('admin.categories.index') }}" class="inline-form admin-toolbar-right admin-search">
                <input type="text" name="q" value="{{ $search }}" placeholder="Search catalogs">
                <button class="button secondary" type="submit">Search</button>
            </form>
        </div>
    </section>

    <section class="grid" style="margin-top: 1.5rem;">
        @foreach ($categories as $category)
            <article class="card">
                <div class="product-foot">
                    <div>
                        <h2>{{ $category->name }}</h2>
                        <p class="muted">Slug: {{ $category->slug }}</p>
                        <p class="muted">{{ $category->description }}</p>
                        <p class="muted">
                            Status:
                            @if ($category->trashed())
                                Archived
                            @else
                                Active
                            @endif
                            · {{ $category->products_count }} shirts
                        </p>
                    </div>

                    <div class="actions">
                        @if (! $category->trashed())
                            <a class="button secondary" href="{{ route('admin.categories.edit', $category) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Archive</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.categories.restore', $category->slug) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit">Restore</button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    @if ($categories->hasPages())
        <div class="pagination-wrap">
            {{ $categories->links() }}
        </div>
    @endif
@endsection
