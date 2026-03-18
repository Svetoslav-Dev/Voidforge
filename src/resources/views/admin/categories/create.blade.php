@extends('layouts.app', ['title' => 'Add Catalog | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <p class="muted">Admin Catalogs</p>
            <h1>Add a new catalog</h1>
        </div>
    </section>

    <section class="card" style="margin-top: 0.6rem;">
        @include('admin.categories.form-fields', [
            'action' => route('admin.categories.store'),
            'buttonLabel' => 'Create catalog',
            'category' => null,
            'method' => null,
        ])
    </section>
@endsection
