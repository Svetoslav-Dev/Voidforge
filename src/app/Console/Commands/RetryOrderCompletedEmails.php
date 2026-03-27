<?php

namespace App\Console\Commands;

use App\Services\OrderCompletionMailService;
use Illuminate\Console\Command;

class RetryOrderCompletedEmails extends Command
{
    protected $signature = 'orders:retry-completed-emails';

    protected $description = 'Queue retry delivery for paid orders with failed or pending completion emails.';

    public function handle(OrderCompletionMailService $mailService): int
    {
        $count = $mailService->retryPendingAndFailed();

        $this->info('Queued '.$count.' order completion email '.($count === 1 ? 'retry.' : 'retries.'));

        return self::SUCCESS;
    }
}
