<?php

namespace App\Http\Middleware;

use App\Models\Order;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrderOwnerOrAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $orderReference = (string) $request->route('orderReference', '');
        $normalized = strtoupper(trim($orderReference));

        abort_unless(str_starts_with($normalized, 'VF'), 404);

        $orderId = (int) substr($normalized, 2);
        abort_unless($orderId > 0, 404);

        $order = Order::query()->findOrFail($orderId);
        $user = $request->user();

        abort_unless(
            $user !== null && ($user->is_admin || $order->user_id === $user->id),
            404
        );

        $request->attributes->set('authorizedOrder', $order);

        return $next($request);
    }
}
