<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class AdminUserService
{
    /**
     * Create a user from validated admin input.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        return User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_admin' => (bool) ($data['is_admin'] ?? false),
        ]);
    }

    /**
     * Soft delete a user while protecting the current admin session.
     *
     * @throws ValidationException
     */
    public function delete(User $user, User $actingUser): void
    {
        if ($user->is($actingUser)) {
            throw ValidationException::withMessages([
                'user' => 'You cannot archive your own account.',
            ]);
        }

        $user->delete();
    }

    /**
     * Restore a soft-deleted user while protecting the current admin session.
     *
     * @throws ValidationException
     */
    public function restore(User $user, User $actingUser): User
    {
        if ($user->is($actingUser)) {
            throw ValidationException::withMessages([
                'user' => 'You cannot change your own archive state.',
            ]);
        }

        $user->restore();

        return $user->fresh();
    }
}
