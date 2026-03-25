<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewSubscriberNotification extends Mailable implements ShouldQueue
{
    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    use Queueable, SerializesModels;

    public function __construct(
        public string $bakerName,
        public string $bakerEmail,
        public string $storeName,
        public string $subdomain,
        public string $plan,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New KneadIt Signup — {$this->storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-subscriber-notification',
        );
    }
}
