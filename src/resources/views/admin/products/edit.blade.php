@extends('layouts.app', ['title' => 'Edit Shirt | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <p class="muted">Admin Shirts</p>
            <h1>Edit {{ $product->name }}</h1>
        </div>
    </section>

    <section class="card" style="margin-top: 0.6rem;">
        @include('admin.products.form-fields', [
            'action' => route('admin.products.update', $product),
            'buttonLabel' => 'Save shirt',
            'product' => $product,
            'method' => 'PATCH',
            'categories' => $categories,
        ])
    </section>
@endsection
