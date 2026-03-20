<div class="field">
    <label for="code">Code</label>
    <input id="code" name="code" type="text" value="{{ old('code', $discountCode->code ?? '') }}" required>
    @error('code')<p class="muted">{{ $message }}</p>@enderror
</div>

<div class="field">
    <label for="description">Description</label>
    <input id="description" name="description" type="text" value="{{ old('description', $discountCode->description ?? '') }}">
    @error('description')<p class="muted">{{ $message }}</p>@enderror
</div>

<div class="grid two">
    <div class="field">
        <label for="type">Type</label>
        <select id="type" name="type" required>
            <option value="percent" @selected(old('type', $discountCode->type ?? 'percent') === 'percent')>Percent</option>
            <option value="fixed" @selected(old('type', $discountCode->type ?? '') === 'fixed')>Fixed EUR</option>
        </select>
        @error('type')<p class="muted">{{ $message }}</p>@enderror
    </div>

    <div class="field">
        <label for="amount">Amount</label>
        <input
            id="amount"
            name="amount"
            type="text"
            value="{{ old('amount', isset($discountCode) ? ($discountCode->type === \App\Models\DiscountCode::TYPE_FIXED ? number_format($discountCode->amount / 100, 2, '.', '') : $discountCode->amount) : '') }}"
            required
        >
        @error('amount')<p class="muted">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid two">
    <div class="field">
        <label for="usage_limit">Usage limit</label>
        <input id="usage_limit" name="usage_limit" type="number" min="1" value="{{ old('usage_limit', $discountCode->usage_limit ?? '') }}">
        @error('usage_limit')<p class="muted">{{ $message }}</p>@enderror
    </div>

    <div class="field">
        <label for="expires_at">Expires at</label>
        <input id="expires_at" name="expires_at" type="datetime-local" value="{{ old('expires_at', isset($discountCode) && $discountCode->expires_at ? $discountCode->expires_at->format('Y-m-d\TH:i') : '') }}">
        @error('expires_at')<p class="muted">{{ $message }}</p>@enderror
    </div>
</div>

<label class="checkbox" for="is_active">
    <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $discountCode->is_active ?? true))>
    <span>Active</span>
</label>
