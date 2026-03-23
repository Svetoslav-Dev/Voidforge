@extends('layouts.app', ['title' => 'Orders | Voidforge'])

@section('content')
    <div data-admin-list-search-page>
        <section class="card hero">
            <div>
                <h1 style="color: #d89a58;">Review customer orders</h1>
                <p class="lead">This view keeps order oversight simple: status, customer, total, and a drill-down page.</p>
            </div>

            <div class="admin-toolbar">
                <div class="admin-toolbar-left">
                    <a class="button secondary" href="{{ route('admin.panel') }}">Back to admin</a>
                </div>

                <form method="GET" action="{{ route('admin.orders.index') }}" class="inline-form admin-toolbar-right admin-search" data-admin-list-search-form>
                    <input type="text" name="q" value="{{ $search }}" placeholder="Search orders">
                    <button class="button secondary" type="submit">Search</button>
                </form>
            </div>
        </section>

        <section class="grid">
            @foreach ($orders as $order)
                <article class="card" style="padding: 1.15rem 1.25rem;">
                    <div class="product-foot">
                        <div>
                            <h2>
                                Order #VF{{ $order->id }}
                                @if ((int) $order->user_id === (int) auth()->id())
                                    <span style="color: #ffb86b; font-size: 0.8em;">- Current user</span>
                                @endif
                            </h2>
                            <p class="muted">{{ $order->customer_name }} · {{ $order->customer_email }}</p>
                            <p class="muted">
                                Placed:
                                {{ optional($order->placed_at)->format('M d, Y H:i') ?? $order->created_at->format('M d, Y H:i') }}
                            </p>
                            <p class="muted">
                                Status:
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                @if ($order->trashed())
                                    · Archived
                                @endif
                            </p>
                            <p class="muted">
                                Payment:
                                {{ $order->payments->first()?->provider ? ucfirst($order->payments->first()->provider) : 'Awaiting payment' }}
                            </p>
                        </div>

                        <div class="actions" style="align-items: center; margin-top: 0;">
                            <strong>{{ number_format($order->total_cents / 100, 2) }} EUR</strong>
                            @if (! $order->trashed())
                                <a class="button secondary" href="{{ route('admin.orders.show', $order) }}">Open order</a>
                                <form method="POST" action="{{ route('admin.orders.destroy', $order) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Archive</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.orders.restore', $order->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit">Restore</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        @if ($orders->hasPages())
            <div class="pagination-wrap">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
@endsection
