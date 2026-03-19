<?php

namespace App\Services;

use App\Mail\OrderCompletedMail;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OrderCompletionMailService
{
    public function __construct(
        private readonly ReceiptPdfService $receiptPdfService
    ) {
    }

    /**
     * Send the completed-order email once for a paid order.
     */
    public function sendFor(Order $order): void
    {
        $order = Order::query()
            ->with(['items.product', 'payments'])
            ->find($order->id);

        if (! $order || $order->status !== 'paid' || $order->placed_at === null || $order->receipt_emailed_at !== null) {
            return;
        }

        try {
            Mail::to($order->customer_email)->send(
                new OrderCompletedMail($order, $this->receiptPdfService->render($order))
            );
        } catch (Throwable $exception) {
            report($exception);

            return;
        }

        $order->forceFill([
            'receipt_emailed_at' => now(),
        ])->save();
    }
}
