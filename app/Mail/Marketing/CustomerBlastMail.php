<?php

namespace App\Mail\Marketing;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CustomerBlastMail extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public string $campaignSubject,
        public string $campaignBody,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: $this->campaignSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.marketing.customer-blast',
            with: [
                'body' => $this->campaignBody,
            ],
        );
    }
}
