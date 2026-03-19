<?php

namespace App\Http\Middleware;

use App\Services\CartService;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCartIsNotEmpty
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if (app(CartService::class)->items()->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('status', 'Your cart is empty.');
        }

        return $next($request);
    }
}
