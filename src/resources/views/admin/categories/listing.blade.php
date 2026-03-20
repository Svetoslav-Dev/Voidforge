@extends('layouts.app', ['title' => 'Categories | Voidforge'])

@section('content')
    @php
        $categoryErrorFields = ['name', 'slug', 'description'];
        $hasCategoryErrors = collect($categoryErrorFields)->contains(fn (string $field) => $errors->has($field));
        $categoryModalMode = old('_category_context', 'create');
        $isCategoryEditMode = $categoryModalMode === 'edit';
        $categoryFormAction = $isCategoryEditMode
            ? old('_category_update_url', route('admin.categories.store'))
            : route('admin.categories.store');
        $categoryModalTitle = $isCategoryEditMode
            ? 'Edit '.old('name', 'category')
            : 'Add category';
        $categorySubmitLabel = $isCategoryEditMode ? 'Save category' : 'Create category';
    @endphp

    <section class="card hero">
        <div>
            <h1>Manage the storefront categories.</h1>
            <p class="lead">Create, edit, and archive categories without removing their history from the database.</p>
        </div>

        <div class="admin-toolbar">
            <div class="admin-toolbar-left">
                <a class="button secondary" href="{{ route('admin.panel') }}">Back to admin</a>
                <button class="button" type="button" data-admin-category-create-open>Add category</button>
            </div>

            <form method="GET" action="{{ route('admin.categories.index') }}" class="inline-form admin-toolbar-right admin-search">
                <input type="text" name="q" value="{{ $search }}" placeholder="Search categories">
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
                            <button
                                class="button secondary"
                                type="button"
                                data-admin-category-edit-open
                                data-update-url="{{ route('admin.categories.update', $category) }}"
                                data-name="{{ $category->name }}"
                                data-slug="{{ $category->slug }}"
                                data-description="{{ $category->description }}"
                            >Edit</button>
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

    <div class="account-address-modal" data-admin-category-modal {{ $hasCategoryErrors ? '' : 'hidden' }}>
        <div class="account-address-modal__backdrop" data-admin-category-close></div>
        <div class="card account-address-modal__panel" role="dialog" aria-modal="true" aria-labelledby="admin-category-modal-title">
            <h2 id="admin-category-modal-title" style="margin-top: 0;" data-admin-category-modal-title>{{ $categoryModalTitle }}</h2>

            <form method="POST" action="{{ $categoryFormAction }}" data-admin-category-form data-store-url="{{ route('admin.categories.store') }}">
                @csrf
                <input type="hidden" name="_method" value="PATCH" data-admin-category-method {{ $isCategoryEditMode ? '' : 'disabled' }}>
                <input type="hidden" name="_category_context" value="{{ $categoryModalMode }}" data-admin-category-context>
                <input type="hidden" name="_category_update_url" value="{{ old('_category_update_url', '') }}" data-admin-category-update-url>
                @include('admin.categories.fields', ['category' => null])

                <div class="actions account-address-modal__actions">
                    <button type="button" class="button danger" data-admin-category-close>Close</button>
                    <button type="submit" data-admin-category-submit>{{ $categorySubmitLabel }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
