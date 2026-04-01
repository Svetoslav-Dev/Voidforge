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
        $status = $request->query('status');
        $allowedStatuses = ['paid', 'awaiting_payment'];
        $activeStatus = in_array($status, $allowedStatuses, true) ? $status : null;

        $query = $request->user()
            ->orders()
            ->with(['items', 'payments'])
            ->orderByRaw('COALESCE(placed_at, created_at) DESC');

        if ($activeStatus !== null) {
            $query->where('status', $activeStatus);
        }

        return view('orders.history', [
            'orders' => $query->paginate(10),
            'activeStatus' => $activeStatus,
        ]);
    }

    /**
     * Show a receipt for one of the authenticated user's orders.
     */
    public function show(Request $request, string $orderReference): View
    {
        $order = $request->attributes->get('authorizedOrder');
        abort_unless($order instanceof Order, 404);

        return view('orders.receipt', [
            'order' => $order->load(['items.product', 'payments']),
        ]);
    }

    /**
     * Download a PDF receipt for one of the authenticated user's orders.
     */
    public function download(Request $request, string $orderReference, ReceiptPdfService $pdfs): Response
    {
        $order = $request->attributes->get('authorizedOrder');
        abort_unless($order instanceof Order && $order->placed_at !== null, 404);

        $order->load(['items.product', 'payments']);
        $pdf = $pdfs->render($order);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="voidforge-receipt-VF'.$order->id.'.pdf"',
        ]);
    }

}
