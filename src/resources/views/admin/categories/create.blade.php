@extends('layouts.app', ['title' => 'Add Category | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <h1>Add a new category</h1>
        </div>
    </section>

    <section class="card" style="margin-top: 0.6rem;">
        @include('admin.categories.form-fields', [
            'action' => route('admin.categories.store'),
            'buttonLabel' => 'Create category',
            'category' => null,
            'method' => null,
        ])
    </section>
@endsection
