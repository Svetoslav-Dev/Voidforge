<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class PruneAbandonedOrders extends Command
{
    protected $signature = 'orders:prune-abandoned';

    protected $description = 'Soft-delete awaiting_payment orders older than 30 days.';

    public function handle(): int
    {
        $count = Order::query()
            ->where('status', 'awaiting_payment')
            ->where('created_at', '<', now()->subDays(30))
            ->get()
            ->each(fn (Order $order) => $order->delete())
            ->count();

        $this->info("Archived {$count} abandoned order(s).");

        return self::SUCCESS;
    }
}
