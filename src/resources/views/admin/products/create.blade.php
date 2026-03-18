@extends('layouts.app', ['title' => 'Add Shirt | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <p class="muted">Admin Shirts</p>
            <h1>Add a new shirt.</h1>
        </div>
    </section>

    <section class="card" style="margin-top: 0.6rem;">
        @include('admin.products.form-fields', [
            'action' => route('admin.products.store'),
            'buttonLabel' => 'Create shirt',
            'product' => null,
            'method' => null,
            'categories' => $categories,
        ])
    </section>
@endsection
