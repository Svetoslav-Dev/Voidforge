<?php

use App\Services\OrderCompletionMailService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('orders:retry-completed-emails', function (OrderCompletionMailService $mailService) {
    $count = $mailService->retryPendingAndFailed();

    $this->info('Queued '.$count.' order completion email '.($count === 1 ? 'retry.' : 'retries.'));
})->purpose('Queue retry delivery for paid orders with failed or pending completion emails.');
