<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LegalContactRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly array $payload
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'VoidForgeStore contact request: '.$this->topicLabel(),
            replyTo: [$this->payload['email']],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.legal.contact-request',
        );
    }

    private function topicLabel(): string
    {
        return match ($this->payload['topic']) {
            'privacy' => 'Privacy',
            'returns' => 'Returns',
            'order_support' => 'Order support',
            default => 'General',
        };
    }
}
