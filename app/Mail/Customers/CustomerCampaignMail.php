<?php

namespace App\Mail\Customers;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Engagement\CustomerCampaign;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CustomerCampaignMail extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public CustomerCampaign $campaign,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: $this->campaign->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customers.customer-campaign',
            with: [
                'campaign' => $this->campaign,
            ],
        );
    }
}
