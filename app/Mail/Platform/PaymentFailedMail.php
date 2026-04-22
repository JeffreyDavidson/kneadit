<?php

namespace App\Mail\Platform;

use App\Mail\BaseMailable;
use App\Models\Staff\User;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PaymentFailedMail extends BaseMailable
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
        );
    }
}
