@extends('layouts.app', ['title' => 'Edit Category | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <h1>Edit {{ $category->name }}</h1>
        </div>
    </section>

    <section class="card" style="margin-top: 0.6rem;">
        @include('admin.categories.form-fields', [
            'action' => route('admin.categories.update', $category),
            'buttonLabel' => 'Save category',
            'category' => $category,
            'method' => 'PATCH',
        ])
    </section>
@endsection
