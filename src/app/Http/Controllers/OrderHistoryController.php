<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use App\Services\ReceiptPdfService;

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
                ->orderByRaw('COALESCE(placed_at, created_at) DESC')
                ->get(),
        ]);
    }

    /**
     * Show a receipt for one of the authenticated user's orders.
     */
    public function show(Request $request, string $orderReference): View
    {
        $order = $this->resolveOrderReference($orderReference);

        abort_unless($this->canAccessOrder($request, $order), 404);

        return view('orders.receipt', [
            'order' => $order->load(['items.product', 'payments']),
        ]);
    }

    /**
     * Download a PDF receipt for one of the authenticated user's orders.
     */
    public function download(Request $request, string $orderReference, ReceiptPdfService $pdfs): Response
    {
        $order = $this->resolveOrderReference($orderReference);

        abort_unless($this->canAccessOrder($request, $order) && $order->placed_at !== null, 404);

        $order->load(['items.product', 'payments']);
        $pdf = $pdfs->render($order);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="voidforge-receipt-VF'.$order->id.'.pdf"',
        ]);
    }

    /**
     * Determine whether the current user can access the given order.
     */
    private function canAccessOrder(Request $request, Order $order): bool
    {
        $user = $request->user();

        return $user !== null
            && ($user->is_admin || $order->user_id === $user->id);
    }

    /**
     * Resolve a public VF order reference to an order model.
     */
    private function resolveOrderReference(string $orderReference): Order
    {
        $normalized = strtoupper(trim($orderReference));

        abort_unless(str_starts_with($normalized, 'VF'), 404);

        $orderId = (int) substr($normalized, 2);
        abort_unless($orderId > 0, 404);

        return Order::query()->findOrFail($orderId);
    }
}
