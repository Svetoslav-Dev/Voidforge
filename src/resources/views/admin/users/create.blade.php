@extends('layouts.app', ['title' => 'Add User | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <h1>Add a new user</h1>
            <p class="lead">Create either a regular customer account or an admin account from the dashboard.</p>
        </div>
    </section>

    <section class="card" style="margin-top: 0.6rem;">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            @include('admin.users.form-fields', ['user' => null])

            <div class="actions">
                <a class="button secondary" href="{{ route('admin.users.index') }}">Back</a>
                <button type="submit">Create user</button>
            </div>
        </form>
    </section>
@endsection
