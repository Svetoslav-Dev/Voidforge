@extends('layouts.app', ['title' => 'Dashboard | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <h1 style="font-size: clamp(1.55rem, 3vw, 2.2rem); text-align: center; color: #d89a58;">Rule the void storefront</h1>
            <p class="lead">Manage the active shirt lineup, edit catalogs, archive records logically, and review incoming orders from one protected area.</p>
        </div>

        <div class="actions admin-panel-actions" style="margin-top: 0.85rem;">
            <a class="button secondary" href="{{ route('admin.products.index') }}">Browse shirts</a>
            <a class="button secondary" href="{{ route('admin.categories.index') }}">Browse categories</a>
            <a class="button secondary" href="{{ route('admin.users.index') }}">Browse users</a>
            <a class="button secondary" href="{{ route('admin.discount-codes.index') }}">Browse discounts</a>
            <a class="button secondary" href="{{ route('admin.orders.index') }}">Browse orders</a>
        </div>
    </section>

    <section class="grid three">
        <article class="card">
            <h2 style="color: #d89a58;">Shirts</h2>
            <p class="muted">{{ $activeProductCount }} active shirts with {{ $archivedProductCount }} archived.</p>
        </article>

        <article class="card">
            <h2 style="color: #d89a58;">Categories</h2>
            <p class="muted">{{ $categoryCount }} active categories with {{ $archivedCategoryCount }} archived.</p>
        </article>

        <article class="card">
            <h2 style="color: #d89a58;">Users</h2>
            <p class="muted">{{ $userCount }} active users with {{ $archivedUserCount }} archived.</p>
        </article>

        <article class="card">
            <h2 style="color: #d89a58;">Discounts</h2>
            <p class="muted">{{ $discountCodeCount }} discount codes available for carts and checkout.</p>
        </article>

        <article class="card">
            <h2 style="color: #d89a58;">Orders</h2>
            <p class="muted">{{ $pendingOrderCount }} pending orders with {{ $archivedOrderCount }} archived.</p>
        </article>
    </section>

    <section class="card" style="margin-top: 1.5rem;">
        <div style="display: flex; justify-content: space-between; gap: 1rem; align-items: end; flex-wrap: wrap;">
            <div>
                <h2 style="margin: 0; color: #d89a58;">Revenue this year</h2>
                <p class="muted" style="margin: 0.35rem 0 0;">Paid orders received in {{ $revenueChartYear }} compared with {{ $revenueChartPreviousYear }} by month.</p>
            </div>
            <div style="display: grid; gap: 0.2rem; justify-items: end;">
                <p style="margin: 0; font-weight: 700;">
                    <span style="display: inline-block; min-width: 3.5rem; text-align: left; color: #56a87e; font-weight: 700;">{{ $revenueChartYear }}</span>
                    <span>{{ number_format($yearlyRevenueTotalCents / 100, 2) }} EUR</span>
                </p>
                <p style="margin: 0; font-weight: 700;">
                    <span style="display: inline-block; min-width: 3.5rem; text-align: left; color: #7a62c0; font-weight: 700;">{{ $revenueChartPreviousYear }}</span>
                    <span>{{ number_format($yearlyRevenuePreviousTotalCents / 100, 2) }} EUR</span>
                </p>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; margin-top: 1rem;">
            <div style="display: inline-flex; gap: 0.45rem; align-items: center;">
                <span style="width: 0.9rem; height: 0.9rem; border-radius: 999px; background: linear-gradient(180deg, rgba(86, 168, 126, 0.92) 0%, rgba(20, 63, 47, 0.98) 100%);"></span>
                <span class="muted">{{ $revenueChartYear }}</span>
            </div>
            <div style="display: inline-flex; gap: 0.45rem; align-items: center;">
                <span style="width: 0.9rem; height: 0.9rem; border-radius: 999px; background: linear-gradient(180deg, rgba(122, 98, 192, 0.9) 0%, rgba(44, 29, 88, 0.98) 100%);"></span>
                <span class="muted">{{ $revenueChartPreviousYear }}</span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 0.65rem; align-items: end; margin-top: 1.25rem;">
            @foreach ($monthlyRevenue as $index => $month)
                @php($previousMonth = $monthlyRevenueLastYear[$index])
                @php($currentHeightPercent = ($month['total_cents'] / $yearlyRevenueMaxCents) * 100)
                @php($previousHeightPercent = ($previousMonth['total_cents'] / $yearlyRevenueMaxCents) * 100)
                <div style="display: grid; gap: 0.5rem; align-items: end; justify-items: center;">
                    <div style="display: grid; gap: 0.12rem; justify-items: center; font-size: 0.82rem;">
                        <span style="color: #56a87e;">{{ number_format($month['total_cents'] / 100, 2) }}</span>
                        <span style="color: #7a62c0;">{{ number_format($previousMonth['total_cents'] / 100, 2) }}</span>
                    </div>
                    <div style="width: 100%; height: 14rem; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.25rem; align-items: end;">
                        <div
                            title="{{ $month['label'] }} {{ $revenueChartYear }}: {{ number_format($month['total_cents'] / 100, 2) }} EUR"
                            style="
                                width: 100%;
                                min-height: 0;
                                height: {{ $month['total_cents'] > 0 ? max(6, $currentHeightPercent) : 0 }}%;
                                border-radius: 14px 14px 0 0;
                                border: 0;
                                background: {{ $month['total_cents'] > 0 ? 'linear-gradient(180deg, rgba(86, 168, 126, 0.92) 0%, rgba(20, 63, 47, 0.98) 100%)' : 'transparent' }};
                                box-shadow: {{ $month['total_cents'] > 0 ? 'inset 0 1px 0 rgba(190, 255, 223, 0.16), 0 12px 28px rgba(9, 31, 22, 0.32)' : 'none' }};
                            "
                        ></div>
                        <div
                            title="{{ $previousMonth['label'] }} {{ $revenueChartPreviousYear }}: {{ number_format($previousMonth['total_cents'] / 100, 2) }} EUR"
                            style="
                                width: 100%;
                                min-height: 0;
                                height: {{ $previousMonth['total_cents'] > 0 ? max(6, $previousHeightPercent) : 0 }}%;
                                border-radius: 14px 14px 0 0;
                                border: 0;
                                background: {{ $previousMonth['total_cents'] > 0 ? 'linear-gradient(180deg, rgba(122, 98, 192, 0.9) 0%, rgba(44, 29, 88, 0.98) 100%)' : 'transparent' }};
                                box-shadow: {{ $previousMonth['total_cents'] > 0 ? 'inset 0 1px 0 rgba(224, 214, 255, 0.14), 0 12px 28px rgba(19, 11, 42, 0.28)' : 'none' }};
                            "
                        ></div>
                    </div>
                    <p style="margin: 0; font-weight: 700; color: #d89a58;">{{ $month['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endsection
