<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    public function index(): View
    {
        return view('order-tracking', ['order' => null, 'searched' => false]);
    }

    public function search(Request $request): View
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'order_reference' => ['required', 'string', 'max:20'],
        ]);

        $email = $request->input('email');
        $ref = strtoupper(trim($request->input('order_reference')));
        $ref = ltrim($ref, '#');
        $ref = ltrim($ref, 'VF');
        $id = (int) $ref;

        $order = null;

        if ($id > 0) {
            $order = Order::query()
                ->with(['items', 'payments'])
                ->where('id', $id)
                ->whereRaw('LOWER(customer_email) = ?', [strtolower($email)])
                ->whereNotNull('placed_at')
                ->first();
        }

        return view('order-tracking', ['order' => $order, 'searched' => true]);
    }
}
