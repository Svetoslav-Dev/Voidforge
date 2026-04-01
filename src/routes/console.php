<?php

use App\Services\CartReminderService;
use App\Services\OrderCompletionMailService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('orders:retry-completed-emails', function (OrderCompletionMailService $mailService) {
    $count = $mailService->retryPendingAndFailed();

    $this->info('Queued '.$count.' order completion email '.($count === 1 ? 'retry.' : 'retries.'));
})->purpose('Queue retry delivery for paid orders with failed or pending completion emails.');

Artisan::command('cart:send-reminders', function (CartReminderService $cartReminders) {
    $count = $cartReminders->queueDueReminders();

    $this->info('Queued '.$count.' cart reminder '.($count === 1 ? 'email.' : 'emails.'));
})->purpose('Queue abandoned cart reminder emails for signed-in users.');

Schedule::command('orders:retry-completed-emails')->hourly();
Schedule::command('cart:send-reminders')->daily();
Schedule::command('sitemap:generate')->daily();
Schedule::command('orders:prune-abandoned')->monthly();
