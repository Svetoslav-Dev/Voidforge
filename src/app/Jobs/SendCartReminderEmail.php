<?php

namespace App\Jobs;

use App\Mail\CartReminderMail;
use App\Models\CartReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendCartReminderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /**
     * @var list<int>
     */
    public array $backoff = [60, 300, 900, 1800];

    public function __construct(
        public readonly int $cartReminderId
    ) {
    }

    public function handle(): void
    {
        $cartReminder = CartReminder::query()
            ->with('user')
            ->find($this->cartReminderId);

        if (! $cartReminder || ! $cartReminder->user) {
            return;
        }

        Mail::to($cartReminder->user->email)->send(new CartReminderMail($cartReminder));
    }
}
