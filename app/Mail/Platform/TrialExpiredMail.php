<?php

namespace App\Mail\Platform;

use App\Mail\BaseMailable;
use App\Models\Staff\User;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TrialExpiredMail extends BaseMailable
{
    public function __construct(
        public User $user,
        public string $tenantId,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your KneadIt trial has expired',
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.platform.trial-expired-text',
        );
    }
}
