<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\OrderCompletionMailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendOrderCompletedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /**
     * @var list<int>
     */
    public array $backoff = [60, 300, 900, 1800];

    public function __construct(
        public readonly int $orderId
    ) {
    }

    public function handle(OrderCompletionMailService $mailService): void
    {
        $order = Order::query()->find($this->orderId);

        if (! $order) {
            return;
        }

        $mailService->deliver($order);
    }

    public function failed(Throwable $exception): void
    {
        $order = Order::query()->find($this->orderId);

        if (! $order || $order->receipt_emailed_at !== null) {
            return;
        }

        report($exception);

        $order->forceFill([
            'email_status' => Order::EMAIL_STATUS_FAILED,
        ])->save();
    }
}
