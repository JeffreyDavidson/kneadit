<?php

declare(strict_types=1);

namespace App\Mail\Platform;

use App\Mail\PlatformMailable;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PaymentFailedAlertMail extends PlatformMailable
{
    public function __construct(
        public User $user,
        public ?Tenant $tenant,
        public float $amount,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "⚠️ Payment Failed — {$this->user->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.platform.payment-failed-alert-text',
        );
    }
}
