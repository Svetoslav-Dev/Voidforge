<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LegalContactRequestAcknowledgementMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly array $payload
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'VoidForgeStore request received',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.legal.contact-request-acknowledgement',
        );
    }
}
