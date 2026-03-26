<?php

namespace App\Mail;

use App\Mail\Concerns\BakerBranded;
use App\Models\CateringInquiry;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CateringQuote extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public CateringInquiry $inquiry,
    ) {}

    public function envelope(): Envelope
    {
        $storeName = settings('store_name', 'KneadIt Bakery');

        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: "Your Catering Quote — {$storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.catering-quote',
            with: [
                'inquiry' => $this->inquiry,
            ],
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
