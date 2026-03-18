@extends('layouts.app', ['title' => 'Dashboard | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <p class="muted">Admin Dashboard</p>
            <h1>Browse shirts, catalogs, users, and review orders.</h1>
            <p class="lead">Manage the active shirt lineup, edit catalogs, archive records logically, and review incoming orders from one protected area.</p>
        </div>

        <div class="actions">
            <a class="button secondary" href="{{ route('admin.products.index') }}">Browse shirts</a>
            <a class="button secondary" href="{{ route('admin.categories.index') }}">Browse catalogs</a>
            <a class="button secondary" href="{{ route('admin.users.index') }}">Browse users</a>
            <a class="button secondary" href="{{ route('admin.orders.index') }}">View orders</a>
        </div>
    </section>

    <section class="grid three">
        <article class="card">
            <h2>Shirts</h2>
            <p class="muted">{{ $activeProductCount }} active shirts with {{ $archivedProductCount }} archived.</p>
        </article>

        <article class="card">
            <h2>Catalogs</h2>
            <p class="muted">{{ $categoryCount }} active catalogs with {{ $archivedCategoryCount }} archived.</p>
        </article>

        <article class="card">
            <h2>Users</h2>
            <p class="muted">{{ $userCount }} active users with {{ $archivedUserCount }} archived.</p>
        </article>

        <article class="card">
            <h2>Orders</h2>
            <p class="muted">{{ $pendingOrderCount }} pending orders with {{ $archivedOrderCount }} archived.</p>
        </article>
    </section>
@endsection
