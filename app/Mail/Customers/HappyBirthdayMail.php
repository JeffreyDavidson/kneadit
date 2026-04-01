<?php

namespace App\Mail\Customers;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Customers\Customer;
use App\Models\Financial\Coupon;
use App\Services\Settings\TenantSettings;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class HappyBirthdayMail extends BaseMailable
{
    use BakerBranded;

    public string $storeName;

    public function __construct(
        public Customer $customer,
        public ?Coupon $coupon = null,
    ) {
        $this->storeName = app(TenantSettings::class)->storeName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: "🎂 Happy Birthday, {$this->customer->name}! A Sweet Gift From {$this->storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customers.happy-birthday',
        );
    }
}
