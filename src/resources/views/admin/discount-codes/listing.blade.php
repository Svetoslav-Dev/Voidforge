@extends('layouts.app', ['title' => 'Discount Codes | Admin'])

@section('content')
    @php
        $discountCodeErrorFields = ['code', 'description', 'type', 'amount', 'usage_limit', 'expires_at', 'is_active'];
        $hasDiscountCodeErrors = collect($discountCodeErrorFields)->contains(fn (string $field) => $errors->has($field));
        $discountCodeModalMode = old('_discount_code_context', 'create');
        $isEditMode = $discountCodeModalMode === 'edit';
        $discountCodeFormAction = $isEditMode
            ? old('_discount_code_update_url', route('admin.discount-codes.store'))
            : route('admin.discount-codes.store');
        $discountCodeModalTitle = $isEditMode
            ? 'Edit '.old('code', 'discount code')
            : 'Add discount code';
        $discountCodeSubmitLabel = $isEditMode ? 'Save code' : 'Add code';
    @endphp

    <div data-admin-discount-codes-page>
        <section class="card hero">
            <div>
                <h1 style="color: #d89a58;">Discount codes</h1>
                <p class="lead">Manage active promotions for the storefront checkout flow.</p>
            </div>

            <div class="admin-toolbar" style="margin-top: 0.5rem;">
                <div class="admin-toolbar-left">
                    <a class="button secondary" href="{{ route('admin.panel') }}">Back to admin</a>
                    <button class="button" type="button" data-discount-code-create-open>Add discount code</button>
                </div>

                <form
                    method="GET"
                    action="{{ route('admin.discount-codes.index') }}"
                    class="inline-form admin-toolbar-right admin-search"
                    data-admin-discount-codes-search-form
                >
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search discount codes">
                    <button class="button secondary" type="submit">Search</button>
                </form>
            </div>
        </section>

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

                        <div class="actions" style="margin: 0;">
                            <button
                                class="button secondary"
                                type="button"
                                data-discount-code-edit-open
                                data-update-url="{{ route('admin.discount-codes.update', $discountCode) }}"
                                data-code="{{ $discountCode->code }}"
                                data-description="{{ $discountCode->description ?? '' }}"
                                data-type="{{ $discountCode->type }}"
                                data-amount="{{ $discountCode->type === \App\Models\DiscountCode::TYPE_FIXED ? number_format($discountCode->amount / 100, 2, '.', '') : $discountCode->amount }}"
                                data-usage-limit="{{ $discountCode->usage_limit ?? '' }}"
                                data-expires-at="{{ $discountCode->expires_at?->format('Y-m-d\TH:i') ?? '' }}"
                                data-is-active="{{ $discountCode->is_active ? '1' : '0' }}"
                            >Edit code</button>

                            @if ($discountCode->is_active)
                                <form method="POST" action="{{ route('admin.discount-codes.archive', $discountCode) }}" data-admin-discount-codes-toggle-form>
                                    @csrf
                                    @method('PATCH')
                                    <button class="button danger" type="submit">Archive code</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.discount-codes.restore', $discountCode) }}" data-admin-discount-codes-toggle-form>
                                    @csrf
                                    @method('PATCH')
                                    <button class="button secondary address-default-button" type="submit">Restore code</button>
                                </form>
                            @endif
                        </div>
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
    </div>

    <div class="account-address-modal" data-discount-code-modal {{ $hasDiscountCodeErrors ? '' : 'hidden' }}>
        <div class="account-address-modal__backdrop" data-discount-code-close></div>
        <div class="card account-address-modal__panel" role="dialog" aria-modal="true" aria-labelledby="discount-code-modal-title">
            <h2 id="discount-code-modal-title" style="margin-top: 0;" data-discount-code-modal-title>{{ $discountCodeModalTitle }}</h2>

            <form method="POST" action="{{ $discountCodeFormAction }}" data-discount-code-form data-store-url="{{ route('admin.discount-codes.store') }}">
                @csrf
                <input type="hidden" name="_method" value="PATCH" data-discount-code-method {{ $isEditMode ? '' : 'disabled' }}>
                <input type="hidden" name="_discount_code_context" value="{{ $discountCodeModalMode }}" data-discount-code-context>
                <input type="hidden" name="_discount_code_update_url" value="{{ old('_discount_code_update_url', '') }}" data-discount-code-update-url>
                @include('admin.discount-codes.form-fields')

                <div class="actions account-address-modal__actions">
                    <button type="button" class="button danger" data-discount-code-close>Close</button>
                    <button type="submit" data-discount-code-submit>{{ $discountCodeSubmitLabel }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
