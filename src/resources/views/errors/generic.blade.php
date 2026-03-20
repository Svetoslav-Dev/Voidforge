@extends('layouts.app', ['title' => $title.' | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <h1>{{ $title }}</h1>
            <p class="lead">{{ $message }}</p>
            <div class="actions" style="margin-top: 1rem;">
                <a class="button secondary" href="{{ route('products.index') }}">Browse shirts</a>
                <a class="button secondary" href="{{ route('home') }}">Voidforge info</a>
            </div>
        </div>
    </section>
@endsection
