<div class="grid two">
    <div class="field">
        <label for="name">Name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $user?->name ?? '') }}" required>
    </div>

    <div class="field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user?->email ?? '') }}" required>
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
            <option value="0" @selected((string) old('is_admin', $user?->is_admin ? '1' : '0') === '0')>Customer</option>
            <option value="1" @selected((string) old('is_admin', $user?->is_admin ? '1' : '0') === '1')>Admin</option>
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
