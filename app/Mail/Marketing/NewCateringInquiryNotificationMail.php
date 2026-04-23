<?php

namespace App\Mail\Marketing;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Customers\CateringInquiry;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewCateringInquiryNotificationMail extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public CateringInquiry $inquiry,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: "New catering inquiry — {$this->inquiry->event_type} for {$this->inquiry->guest_count}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.marketing.new-catering-inquiry-notification',
            with: [
                'inquiry' => $this->inquiry,
            ],
        );
    }
}
