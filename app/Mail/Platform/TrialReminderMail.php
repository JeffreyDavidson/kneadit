<?php

declare(strict_types=1);

namespace App\Mail\Platform;

use App\Mail\PlatformMail;
use App\Models\Staff\User;
use App\Services\Tenants\TenantUrlGenerator;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TrialReminderMail extends PlatformMail
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
            text: 'emails.platform.trial-reminder-text',
            with: [
                'billingUrl' => resolve(TenantUrlGenerator::class)->billingForOwner($this->user),
            ],
        );
    }
}
