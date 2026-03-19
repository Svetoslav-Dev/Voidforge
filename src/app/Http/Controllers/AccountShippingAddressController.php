<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\StoreShippingAddressRequest;
use App\Models\ShippingAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountShippingAddressController extends Controller
{
    /**
     * Display the saved shipping addresses page.
     */
    public function index(): View
    {
        return view('account.addresses', [
            'shippingAddresses' => auth()->user()?->shippingAddresses()->get() ?? collect(),
        ]);
    }

    /**
     * Display the edit form for a saved shipping address.
     */
    public function edit(Request $request, ShippingAddress $shippingAddress): View
    {
        $this->ensureOwnership($request, $shippingAddress);

        return view('account.edit-address', [
            'shippingAddress' => $shippingAddress,
        ]);
    }

    /**
     * Store a shipping address for the authenticated user.
     */
    public function store(StoreShippingAddressRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $shouldBeDefault = (bool) ($data['is_default'] ?? false) || ! $user->shippingAddresses()->exists();

        if ($shouldBeDefault) {
            $user->shippingAddresses()->update(['is_default' => false]);
        }

        $user->shippingAddresses()->create([
            ...$data,
            'is_default' => $shouldBeDefault,
        ]);

        return redirect()
            ->route('account.addresses.index')
            ->with('status', 'Shipping address saved.');
    }

    /**
     * Update a saved shipping address for the authenticated user.
     */
    public function update(StoreShippingAddressRequest $request, ShippingAddress $shippingAddress): RedirectResponse
    {
        $this->ensureOwnership($request, $shippingAddress);

        $user = $request->user();
        $data = $request->validated();
        $shouldBeDefault = (bool) ($data['is_default'] ?? false)
            || ! $user->shippingAddresses()
                ->whereKeyNot($shippingAddress->getKey())
                ->where('is_default', true)
                ->exists();

        if ($shouldBeDefault) {
            $user->shippingAddresses()
                ->whereKeyNot($shippingAddress->getKey())
                ->update(['is_default' => false]);
        }

        $shippingAddress->update([
            ...$data,
            'is_default' => $shouldBeDefault,
        ]);

        return redirect()
            ->route('account.addresses.index')
            ->with('status', 'Shipping address updated.');
    }

    /**
     * Delete a saved shipping address for the authenticated user.
     */
    public function destroy(Request $request, ShippingAddress $shippingAddress): RedirectResponse
    {
        $this->ensureOwnership($request, $shippingAddress);

        $user = $request->user();
        $wasDefault = $shippingAddress->is_default;

        $shippingAddress->update([
            'label' => 'Deleted address',
            'recipient_name' => 'Removed recipient',
            'phone' => null,
            'address_line_1' => 'Redacted address',
            'address_line_2' => null,
            'city' => 'Redacted city',
            'state' => null,
            'postal_code' => '0000',
            'country' => 'BG',
            'is_default' => false,
        ]);

        $shippingAddress->delete();

        if ($wasDefault) {
            $user->shippingAddresses()
                ->oldest('id')
                ->limit(1)
                ->update(['is_default' => true]);
        }

        return redirect()
            ->route('account.addresses.index')
            ->with('status', 'Shipping address archived.');
    }

    /**
     * Ensure the current user owns the shipping address.
     */
    protected function ensureOwnership(Request $request, ShippingAddress $shippingAddress): void
    {
        abort_unless((int) $shippingAddress->user_id === (int) $request->user()?->id, 404);
    }
}
