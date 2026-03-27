<?php

namespace App\Services;

use App\Jobs\SendOrderCompletedEmail;
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

    public function queueFor(Order $order): void
    {
        $order = $this->loadDeliverableOrder($order);

        if (! $order || $order->email_status === Order::EMAIL_STATUS_PENDING) {
            return;
        }

        try {
            $order->forceFill([
                'email_status' => Order::EMAIL_STATUS_PENDING,
            ])->save();

            SendOrderCompletedEmail::dispatch($order->id);
        } catch (Throwable $exception) {
            report($exception);

            $order->forceFill([
                'email_status' => Order::EMAIL_STATUS_FAILED,
            ])->save();
        }
    }

    public function deliver(Order $order): void
    {
        $order = $this->loadDeliverableOrder($order);

        if (! $order) {
            return;
        }

        Mail::to($order->customer_email)->send(
            new OrderCompletedMail($order, $this->receiptPdfService->render($order))
        );

        $order->forceFill([
            'receipt_emailed_at' => now(),
            'email_status' => Order::EMAIL_STATUS_SENT,
        ])->save();
    }

    public function retryPendingAndFailed(): int
    {
        $orders = Order::query()
            ->where('status', 'paid')
            ->whereNotNull('placed_at')
            ->whereNull('receipt_emailed_at')
            ->whereIn('email_status', [
                Order::EMAIL_STATUS_PENDING,
                Order::EMAIL_STATUS_FAILED,
            ])
            ->pluck('id');

        foreach ($orders as $orderId) {
            SendOrderCompletedEmail::dispatch($orderId);
        }

        return $orders->count();
    }

    private function loadDeliverableOrder(Order $order): ?Order
    {
        $order = Order::query()
            ->with(['items.product', 'payments'])
            ->find($order->id);

        if (! $order || $order->status !== 'paid' || $order->placed_at === null || $order->receipt_emailed_at !== null) {
            return null;
        }

        return $order;
    }
}
