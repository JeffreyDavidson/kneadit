<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

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
