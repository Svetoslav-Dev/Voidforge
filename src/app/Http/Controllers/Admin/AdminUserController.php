<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Models\User;
use App\Services\AdminUserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    /**
     * Display the admin user listing.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        return view('admin.users.listing', [
            'users' => User::query()
                ->withTrashed()
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($builder) use ($search) {
                        $builder
                            ->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
                })
                ->orderByDesc('created_at')
                ->paginate(15)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    /**
     * Show the create user form.
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Persist a new user.
     */
    public function store(StoreUserRequest $request, AdminUserService $users): RedirectResponse
    {
        $users->create($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User created.');
    }

    /**
     * Soft delete a user account.
     */
    public function destroy(User $user, AdminUserService $users): RedirectResponse
    {
        $users->delete($user, auth()->user());

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User archived.');
    }

    /**
     * Restore a soft-deleted user account.
     */
    public function restore(int $user, AdminUserService $users): RedirectResponse
    {
        $users->restore(
            User::query()->withTrashed()->findOrFail($user),
            auth()->user()
        );

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User restored.');
    }
}
