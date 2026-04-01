@extends('layouts.app', ['title' => 'Users | Voidforge'])

@section('content')
    @php
        $userErrorFields = ['name', 'email', 'password', 'is_admin'];
        $hasUserFormErrors = collect($userErrorFields)->contains(fn (string $field) => $errors->has($field));
    @endphp

    <div data-admin-list-search-page>
        <section class="card hero">
            <div>
                <h1 style="color: #d89a58;">Browse users and archive accounts logically</h1>
                <p class="lead">Archived users stay in the database with `deleted_at`, so order history and receipts remain intact.</p>
            </div>

            <div class="admin-toolbar">
                <div class="admin-toolbar-left">
                    <a class="button secondary" href="{{ route('admin.panel') }}">Back to admin</a>
                    <button class="button" type="button" data-admin-user-create-open>Add new user</button>
                </div>

                <form method="GET" action="{{ route('admin.users.index') }}" class="inline-form admin-toolbar-right admin-search" data-admin-list-search-form>
                    <input type="text" name="q" value="{{ $search }}" placeholder="Search users">
                    <button class="button secondary" type="submit">Search</button>
                </form>
            </div>
        </section>

        <section class="list-stack" style="margin-top: 1.5rem;">
            @foreach ($users as $user)
                <article class="card">
                    <div class="receipt-header">
                        <div>
                            @if (auth()->id() === $user->id)
                                <p style="margin: 0 0 0.25rem; font-weight: 700; color: #ffb86b;">Current user</p>
                            @endif
                            <p class="muted">{{ $user->email }}</p>
                            <h2 style="margin: 0 0 0.4rem; color: #4ecba3;">{{ $user->name }}</h2>
                            <p class="muted">
                                {{ $user->is_admin ? 'Admin' : 'Customer' }}
                                ·
                                {{ $user->deleted_at ? 'Archived' : 'Active' }}
                                ·
                                Last login:
                                {{ $user->last_login_at?->format('M d, Y H:i') ?? 'Never' }}
                            </p>
                        </div>

                        <div class="actions">
                            @if (! $user->deleted_at && auth()->id() !== $user->id)
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Archive user</button>
                                </form>
                            @elseif ($user->deleted_at && auth()->id() !== $user->id)
                                <form method="POST" action="{{ route('admin.users.restore', $user->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit">Restore user</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        @if ($users->hasPages())
            <div class="pagination-wrap">
                {{ $users->links() }}
            </div>
        @endif

        <div class="account-address-modal" data-admin-user-modal {{ $hasUserFormErrors ? '' : 'hidden' }}>
            <div class="account-address-modal__backdrop" data-admin-user-close></div>
            <div class="card account-address-modal__panel" role="dialog" aria-modal="true" aria-labelledby="admin-user-modal-title">
                <h2 id="admin-user-modal-title" style="margin-top: 0;">Add new user</h2>

                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    @include('admin.users.form-fields', ['user' => null])

                    <div class="actions account-address-modal__actions">
                        <button type="button" class="button danger" data-admin-user-close>Close</button>
                        <button type="submit">Create user</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
