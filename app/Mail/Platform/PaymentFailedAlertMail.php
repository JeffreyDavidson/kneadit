<?php

namespace App\Mail\Platform;

use App\Mail\BaseMailable;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PaymentFailedAlertMail extends BaseMailable
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

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
