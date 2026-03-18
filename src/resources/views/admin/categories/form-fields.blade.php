<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method)
        @method($method)
    @endif

    <div class="field">
        <label for="name">Name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $category?->name) }}" required>
    </div>

    <div class="field">
        <label for="slug">Slug</label>
        <input id="slug" name="slug" type="text" value="{{ old('slug', $category?->slug) }}" required>
    </div>

    <div class="field">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="5" required>{{ old('description', $category?->description) }}</textarea>
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
        <a class="button secondary" href="{{ route('admin.categories.index') }}">Back</a>
        <button type="submit">{{ $buttonLabel }}</button>
    </div>
</form>
