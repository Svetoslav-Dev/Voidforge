@extends('layouts.app', ['title' => 'Add User | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <p class="muted">Admin Users</p>
            <h1>Add a new user</h1>
            <p class="lead">Create either a regular customer account or an admin account from the dashboard.</p>
        </div>
    </section>

    <section class="card" style="margin-top: 0.6rem;">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <div class="grid two">
                <div class="field">
                    <label for="name">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                </div>
            </div>

            <div class="grid two">
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required>
                </div>

                <div class="field">
                    <label for="is_admin">Role</label>
                    <select id="is_admin" name="is_admin">
                        <option value="0" @selected((string) old('is_admin', '0') === '0')>Customer</option>
                        <option value="1" @selected((string) old('is_admin') === '1')>Admin</option>
                    </select>
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
                <a class="button secondary" href="{{ route('admin.users.index') }}">Back</a>
                <button type="submit">Create user</button>
            </div>
        </form>
    </section>
@endsection
