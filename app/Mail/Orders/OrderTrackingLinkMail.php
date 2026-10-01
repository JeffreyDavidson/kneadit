<?php

declare(strict_types=1);

namespace App\Mail\Orders;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Customers\Customer;
use App\Services\Settings\TenantSettings;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderTrackingLinkMail extends BaseMailable
{
    use BakerBranded;

    public const int LINK_LIFETIME_MINUTES = 30;

    public function __construct(
        public Customer $customer,
        public string $trackingUrl,
    ) {}

    public function envelope(): Envelope
    {
        $storeName = resolve(TenantSettings::class)->store->name;

        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: "Your order link — {$storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.order-tracking-link',
            with: [
                'customer' => $this->customer,
                'trackingUrl' => $this->trackingUrl,
                'lifetimeMinutes' => self::LINK_LIFETIME_MINUTES,
            ],
        );
    }
}
