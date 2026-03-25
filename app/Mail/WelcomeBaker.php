<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeBaker extends Mailable implements ShouldQueue
{
    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    use Queueable, SerializesModels;

    public function __construct(
        public string $bakerName,
        public string $storeName,
        public string $adminUrl,
        public string $plan,
        public string $trialEndsAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Welcome to KneadIt — {$this->storeName} is ready!",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-baker',
        );
    }
}
