<?php

namespace App\Mail\Customers;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Mail\Concerns\MarketingMail;
use App\Mail\Concerns\SendsMarketingMail;
use App\Models\Customers\Customer;
use App\Models\Engagement\CustomerCampaign;
use App\Services\Customers\MarketingUnsubscribeLinks;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CustomerCampaignMail extends BaseMailable implements MarketingMail
{
    use BakerBranded;
    use SendsMarketingMail;

    public function __construct(
        public CustomerCampaign $campaign,
        public Customer $customer,
        public ?string $trackingToken = null,
    ) {}

    public function unsubscribeUrl(): string
    {
        return resolve(MarketingUnsubscribeLinks::class)->unsubscribe($this->customer);
    }

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
                'trackingPixelUrl' => $this->trackingToken
                    ? route('campaign.track.open', ['token' => $this->trackingToken])
                    : null,
            ],
        );
    }
}
