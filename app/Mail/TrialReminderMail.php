<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TrialReminderMail extends BaseMailable
{
    public function __construct(
        public User $user,
        public string $storeName,
        public int $daysLeft,
    ) {}

    public function envelope(): Envelope
    {
        $subjects = [
            7 => 'Your KneadIt trial ends in 7 days',
            3 => '⏰ 3 days left on your KneadIt trial',
            1 => '🚨 Your KneadIt trial ends tomorrow',
        ];

        return new Envelope(
            subject: $subjects[$this->daysLeft] ?? 'Trial ending soon',
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.trial-reminder-text',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
