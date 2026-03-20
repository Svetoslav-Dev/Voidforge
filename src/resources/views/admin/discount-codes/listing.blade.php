@extends('layouts.app', ['title' => 'Discount Codes | Admin'])

@section('content')
    <section class="card hero">
        <div>
            <h1>Discount codes</h1>
            <p class="lead">Manage active promotions for the storefront checkout flow.</p>
        </div>
    </section>

    <div class="account-addresses-toolbar">
        <div class="actions" style="margin: 0;">
            <a class="button secondary" href="{{ route('admin.panel') }}">Back to admin</a>
            <a class="button" href="{{ route('admin.discount-codes.create') }}">Add discount code</a>
        </div>

        <form method="GET" action="{{ route('admin.discount-codes.index') }}">
            <input type="search" name="q" value="{{ $search }}" placeholder="Search discount codes">
        </form>
    </div>

    <section class="grid">
        @forelse ($discountCodes as $discountCode)
            <article class="card">
                <div style="display:flex; justify-content:space-between; gap:1rem; align-items:start; flex-wrap:wrap;">
                    <div>
                        <h2 style="margin:0;">{{ $discountCode->code }}</h2>
                        @if ($discountCode->description)
                            <p class="muted">{{ $discountCode->description }}</p>
                        @endif
                        <p class="muted">
                            {{ $discountCode->type === \App\Models\DiscountCode::TYPE_PERCENT ? $discountCode->amount.'% off' : number_format($discountCode->amount / 100, 2).' EUR off' }}
                            · {{ $discountCode->is_active ? 'Active' : 'Inactive' }}
                        </p>
                        <p class="muted">
                            Used {{ $discountCode->times_used }} times
                            @if ($discountCode->usage_limit)
                                / {{ $discountCode->usage_limit }}
                            @endif
                        </p>
                        @if ($discountCode->expires_at)
                            <p class="muted">Expires {{ $discountCode->expires_at->format('F d, Y H:i') }}</p>
                        @endif
                    </div>

                    <a class="button secondary" href="{{ route('admin.discount-codes.edit', $discountCode) }}">Edit code</a>
                </div>
            </article>
        @empty
            <article class="card">
                <h2>No discount codes found</h2>
                <p class="muted">Add your first promotion to start applying discounts at checkout.</p>
            </article>
        @endforelse
    </section>

    <div class="pagination-wrap">
        {{ $discountCodes->links() }}
    </div>
@endsection
