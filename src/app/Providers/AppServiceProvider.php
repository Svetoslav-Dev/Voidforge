<?php

namespace App\Providers;

use App\Services\CartService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('auth.login', function (Request $request): Limit {
            return Limit::perMinute(5)->by(
                strtolower((string) $request->input('email')).'|'.$request->ip()
            );
        });

        RateLimiter::for('auth.register', function (Request $request): Limit {
            return Limit::perMinutes(10, 3)->by($request->ip());
        });

        RateLimiter::for('account.addresses.store', function (Request $request): Limit {
            return Limit::perMinutes(10, 10)->by('user:'.$request->user()?->getAuthIdentifier());
        });

        RateLimiter::for('account.payment-methods.store', function (Request $request): Limit {
            return Limit::perMinutes(10, 5)->by('user:'.$request->user()?->getAuthIdentifier());
        });

        RateLimiter::for('checkout.payment-start', function (Request $request): Limit {
            $user = $request->user();
            $key = $user
                ? 'user:'.$user->getAuthIdentifier()
                : 'guest:'.$request->session()->get('checkout.last_order_email', 'unknown').'|'.$request->ip();

            return Limit::perMinutes(10, 10)->by($key);
        });

        View::composer('*', function ($view): void {
            $view->with('cartItemCount', app(CartService::class)->itemCount());
        });
    }
}
