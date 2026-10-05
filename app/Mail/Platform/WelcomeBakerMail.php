<?php

declare(strict_types=1);

namespace App\Mail\Platform;

use App\Mail\PlatformMailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class WelcomeBakerMail extends PlatformMailable
{
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
            view: 'emails.platform.welcome-baker',
        );
    }
}
