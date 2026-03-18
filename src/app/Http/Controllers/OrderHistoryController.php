<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderHistoryController extends Controller
{
    /**
     * Show the authenticated user's purchase history.
     */
    public function index(Request $request): View
    {
        return view('orders.history', [
            'orders' => $request->user()
                ->orders()
                ->with(['items', 'payments'])
                ->latest('placed_at')
                ->latest('id')
                ->get(),
        ]);
    }

    /**
     * Show a receipt for one of the authenticated user's orders.
     */
    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        return view('orders.receipt', [
            'order' => $order->load(['items.product', 'payments']),
        ]);
    }
}
