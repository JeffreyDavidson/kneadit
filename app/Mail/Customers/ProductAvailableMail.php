<?php

namespace App\Mail\Customers;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Inventory\Product;
use App\Services\Settings\TenantSettings;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ProductAvailableMail extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public Product $product,
        public string $customerName = '',
    ) {}

    public function envelope(): Envelope
    {
        $storeName = app(TenantSettings::class)->storeName;

        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: "{$this->product->name} is back at {$storeName}!",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.customers.product-available',
            with: [
                'product' => $this->product,
                'customerName' => $this->customerName,
            ],
        );
    }
}
