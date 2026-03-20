<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureCartIsNotEmpty;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureOrderOwnerOrAdmin;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'cart.not_empty' => EnsureCartIsNotEmpty::class,
            'order.owner_or_admin' => EnsureOrderOwnerOrAdmin::class,
        ]);

        $middleware->preventRequestForgery(except: [
            'webhooks/stripe',
            'webhooks/paypal',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $exception, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            if ($request->user()?->is_admin) {
                return null;
            }

            if (
                $exception instanceof ValidationException
                || $exception instanceof AuthenticationException
                || $exception instanceof AuthorizationException
            ) {
                return null;
            }

            $status = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode()
                : 500;

            return response()->view('errors.generic', [
                'status' => $status,
                'title' => match ($status) {
                    403 => 'Access denied',
                    404 => 'Page not found',
                    default => 'Something went wrong',
                },
                'message' => match ($status) {
                    403 => 'You do not have permission to access this page.',
                    404 => 'The page you requested could not be found.',
                    default => 'An unexpected error occurred. Please try again.',
                },
            ], $status);
        });
    })->create();
