<?php

declare(strict_types=1);

namespace App\Mail\Marketing;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Mail\Concerns\MarketingMail;
use App\Mail\Concerns\SendsMarketingMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CustomerBlastMail extends BaseMailable implements MarketingMail
{
    use BakerBranded;
    use SendsMarketingMail;

    public function __construct(
        public string $campaignSubject,
        public string $campaignBody,
        public string $recipientUnsubscribeUrl,
    ) {}

    public function unsubscribeUrl(): string
    {
        return $this->recipientUnsubscribeUrl;
    }

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
