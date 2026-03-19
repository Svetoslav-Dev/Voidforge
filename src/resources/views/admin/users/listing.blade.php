@extends('layouts.app', ['title' => 'Users | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <p class="muted">Admin Users</p>
            <h1>Browse users and archive accounts logically.</h1>
            <p class="lead">Archived users stay in the database with `deleted_at`, so order history and receipts remain intact.</p>
        </div>

        <div class="admin-toolbar">
            <div class="admin-toolbar-left">
                <a class="button" href="{{ route('admin.users.create') }}">Add new user</a>
                <a class="button secondary" href="{{ route('admin.panel') }}">Back to dashboard</a>
            </div>

            <form method="GET" action="{{ route('admin.users.index') }}" class="inline-form admin-toolbar-right admin-search">
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
                        <h2 style="margin: 0 0 0.4rem;">{{ $user->name }}</h2>
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
@endsection
