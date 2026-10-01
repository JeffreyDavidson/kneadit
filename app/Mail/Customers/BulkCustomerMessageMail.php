<?php

declare(strict_types=1);

namespace App\Mail\Customers;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Mail\Concerns\MarketingMail;
use App\Mail\Concerns\SendsMarketingMail;
use App\Models\Customers\Customer;
use App\Services\Customers\MarketingUnsubscribeLinks;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * One-off operational message to a single customer, dispatched in bulk
 * by the Filament "Send message" bulk action. Distinct from
 * CustomerCampaignMail (no campaign record, no open tracking, no
 * recipient log) — intended for ad-hoc messages like "your pickup
 * window changed" or "we have a question about your order".
 */
class BulkCustomerMessageMail extends BaseMailable implements MarketingMail
{
    use BakerBranded;
    use SendsMarketingMail;

    public function __construct(
        public Customer $customer,
        public string $messageSubject,
        public string $body,
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
            subject: $this->messageSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customers.bulk-customer-message',
            with: [
                'customer' => $this->customer,
                'body' => $this->body,
            ],
        );
    }
}
