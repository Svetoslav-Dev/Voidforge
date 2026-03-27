@extends('layouts.app', ['title' => 'Legal Requests | Voidforge'])

@section('content')
    <div data-admin-list-search-page>
        <section class="card hero">
            <div>
                <h1 style="color: #d89a58;">Browse legal and support requests</h1>
                <p class="lead">Review privacy, returns, order-support, and general requests submitted through the public storefront contact form.</p>
            </div>

            <div class="admin-toolbar">
                <div class="admin-toolbar-left">
                    <a class="button secondary" href="{{ route('admin.panel') }}">Back to admin</a>
                </div>

                <form method="GET" action="{{ route('admin.legal-requests.index') }}" class="inline-form admin-toolbar-right admin-search" data-admin-list-search-form>
                    <input type="text" name="q" value="{{ $search }}" placeholder="Search requests">
                    <button class="button secondary" type="submit">Search</button>
                </form>
            </div>
        </section>

        <section class="list-stack" style="margin-top: 1.5rem;">
            @forelse ($requests as $requestItem)
                <article class="card">
                    <div class="receipt-header">
                        <div>
                            <p class="muted">{{ $requestItem->email }}</p>
                            <h2 style="margin: 0 0 0.4rem;">{{ $requestItem->name }}</h2>
                            <p class="muted">
                                {{ str_replace('_', ' ', ucfirst($requestItem->topic)) }}
                                ·
                                {{ $requestItem->resolved_at ? 'Resolved' : 'Open' }}
                                ·
                                {{ $requestItem->created_at->format('M d, Y H:i') }}
                            </p>
                            @if ($requestItem->order_reference)
                                <p class="muted" style="margin-top: 0.35rem;">Order reference: {{ $requestItem->order_reference }}</p>
                            @endif
                            <p style="margin: 0.75rem 0 0;">{{ $requestItem->message }}</p>
                        </div>

                        <div class="actions">
                            @if ($requestItem->resolved_at)
                                <form method="POST" action="{{ route('admin.legal-requests.reopen', $requestItem) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit">Reopen</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.legal-requests.resolve', $requestItem) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit">Mark resolved</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <section class="card empty-state">
                    <h2>No requests yet</h2>
                    <p class="muted">Public legal and support requests will appear here after customers use the contact page.</p>
                </section>
            @endforelse
        </section>

        @if ($requests->hasPages())
            <div class="pagination-wrap">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
@endsection
