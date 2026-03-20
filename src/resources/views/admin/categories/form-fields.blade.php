<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method)
        @method($method)
    @endif

    @include('admin.categories.fields', ['category' => $category])

    <div class="actions">
        <a class="button secondary" href="{{ route('admin.categories.index') }}">Back</a>
        <button type="submit">{{ $buttonLabel }}</button>
    </div>
</form>
