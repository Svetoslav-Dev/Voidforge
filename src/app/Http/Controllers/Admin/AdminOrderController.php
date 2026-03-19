<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\AdminOrderService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    /**
     * Display the admin order listing.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        return view('admin.orders.listing', [
            'orders' => Order::query()
                ->withTrashed()
                ->whereNotNull('placed_at')
                ->with(['user', 'payments'])
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($builder) use ($search) {
                        $builder
                            ->where('customer_name', 'like', '%'.$search.'%')
                            ->orWhere('customer_email', 'like', '%'.$search.'%')
                            ->orWhere('status', 'like', '%'.$search.'%')
                            ->orWhere('id', 'like', '%'.$search.'%');
                    });
                })
                ->orderByRaw('COALESCE(placed_at, created_at) DESC')
                ->paginate(15)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    /**
     * Display a single admin order detail page.
     */
    public function show(Order $order): View
    {
        return view('admin.orders.details', [
            'order' => $order->load(['items', 'payments', 'user']),
        ]);
    }

    /**
     * Soft delete the given order.
     */
    public function destroy(Order $order, AdminOrderService $orders): RedirectResponse
    {
        $orders->delete($order);

        return redirect()
            ->route('admin.orders.index')
            ->with('status', 'Order archived.');
    }

    /**
     * Restore a soft-deleted order.
     */
    public function restore(int $order, AdminOrderService $orders): RedirectResponse
    {
        $orders->restore(
            Order::query()->withTrashed()->findOrFail($order)
        );

        return redirect()
            ->route('admin.orders.index')
            ->with('status', 'Order restored.');
    }
}
