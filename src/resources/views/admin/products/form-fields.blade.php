<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if ($method)
        @method($method)
    @endif

    @php($shirtSizes = \App\Models\Product::shirtSizes())
    @php($disabledSizes = collect(old('disabled_sizes', $product?->disabledShirtSizes() ?? []))->map(fn ($size) => strtoupper((string) $size))->all())

    <div class="card" style="margin-bottom: 1rem;">
        <p class="muted">Artwork Preview</p>
        <div class="grid two" style="margin-top: 0.75rem;">
            <div class="product-visual cart-item-visual">
                <img src="{{ $product?->front_image_url ?? asset('images/items/fallback.svg') }}" alt="{{ $product?->name ?? 'Front shirt preview' }}">
                <p class="muted" style="margin: 0.6rem 0 0;">Front</p>
            </div>

            <div class="product-visual cart-item-visual">
                <img src="{{ $product?->back_image_url ?? $product?->front_image_url ?? asset('images/items/fallback.svg') }}" alt="{{ $product?->name ?? 'Back shirt preview' }}">
                <p class="muted" style="margin: 0.6rem 0 0;">Back</p>
            </div>
        </div>

        <div style="margin-top: 1rem;">
            <h2 style="margin-top: 0;">{{ $product?->name ?? 'New shirt preview' }}</h2>
            <p class="muted">
                Generated artwork is based on the current shirt slug.
                @if ($product?->slug)
                    Current slug: {{ $product->slug }}
                @else
                    Save the shirt with a matching slug to use generated images from `public/images/items`.
                @endif
            </p>
        </div>
    </div>

    <div class="grid two">
        <div class="field">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id">
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) old('category_id', $product?->category_id) === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="name">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name', $product?->name) }}" required>
        </div>
    </div>

    <div class="grid two">
        <div class="field">
            <label for="slug">Slug</label>
            <input id="slug" name="slug" type="text" value="{{ old('slug', $product?->slug) }}" required>
        </div>

        <div class="field">
            <label for="sku">SKU</label>
            <input id="sku" name="sku" type="text" value="{{ old('sku', $product?->sku) }}" required>
        </div>
    </div>

    <div class="field">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="5">{{ old('description', $product?->description) }}</textarea>
    </div>

    <div class="grid two">
        <div class="field">
            <label for="price">Price in euro</label>
            <input
                id="price"
                name="price"
                type="number"
                min="0"
                step="0.01"
                inputmode="decimal"
                value="{{ old('price', isset($product) ? number_format(($product?->price_cents ?? 0) / 100, 2, '.', '') : '') }}"
                required
            >
        </div>

        <div class="field">
            <label for="stock">Stock</label>
            <input id="stock" name="stock" type="number" min="0" value="{{ old('stock', $product?->stock) }}" required>
        </div>
    </div>

    <div class="grid two">
        <div class="field">
            <label for="image">Front image</label>
            <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        </div>

        <div class="field">
            <label for="back_image">Back image</label>
            <input id="back_image" name="back_image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        </div>
    </div>

    <div class="grid two">
        <div class="field">
            <label for="is_active">Visibility</label>
            <select id="is_active" name="is_active">
                <option value="1" @selected((string) old('is_active', (int) ($product?->is_active ?? true)) === '1')>Active</option>
                <option value="0" @selected((string) old('is_active', (int) ($product?->is_active ?? true)) === '0')>Draft</option>
            </select>
        </div>

        <div class="field">
            <label>Image source</label>
            <p class="muted" style="margin: 0.7rem 0 0;">
                Uploaded front and back images override the generated slug-based artwork for this shirt.
            </p>
        </div>
    </div>

    <div class="field">
        <label>Disabled sizes</label>
        <p class="muted" style="margin: 0.25rem 0 0.85rem;">
            Checked sizes are treated as sold out on the shirt page.
        </p>
        <div class="chip-row">
            @foreach ($shirtSizes as $size)
                <label class="chip" style="display: inline-flex; align-items: center; gap: 0.45rem;">
                    <input
                        type="checkbox"
                        name="disabled_sizes[]"
                        value="{{ $size }}"
                        @checked(in_array($size, $disabledSizes, true))
                    >
                    <span>{{ $size }}</span>
                </label>
            @endforeach
        </div>
    </div>

    @if ($errors->any())
        <div class="errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="actions">
        <a class="button secondary" href="{{ route('admin.products.index') }}">Back</a>
        <button type="submit">{{ $buttonLabel }}</button>
    </div>
</form>
