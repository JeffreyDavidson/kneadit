<?php

declare(strict_types=1);

namespace App\Mail\Platform;

use App\Mail\PlatformMail;
use App\Models\Staff\User;
use App\Services\Tenants\TenantUrlGenerator;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PaymentFailedMail extends PlatformMail
{
    public function __construct(
        public User $user,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Payment failed — action needed',
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.platform.payment-failed-text',
            with: [
                'billingUrl' => resolve(TenantUrlGenerator::class)->billingForOwner($this->user),
            ],
        );
    }
}
