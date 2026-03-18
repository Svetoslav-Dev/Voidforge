<?php

namespace App\Services;

use App\Models\Order;

class AdminOrderService
{
    /**
     * Soft delete an order.
     */
    public function delete(Order $order): void
    {
        $order->delete();
    }

    /**
     * Restore a soft-deleted order.
     */
    public function restore(Order $order): Order
    {
        $order->restore();

        return $order->fresh(['items', 'payments', 'user']);
    }
}
