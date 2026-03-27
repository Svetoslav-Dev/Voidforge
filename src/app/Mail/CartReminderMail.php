<?php

namespace App\Mail;

use App\Models\CartReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CartReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly CartReminder $cartReminder
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'VoidForgeStore cart reminder',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cart.reminder',
        );
    }
}
