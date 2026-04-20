<?php

namespace App\Mail\Customers;

use App\Enums\Marketing\EmailTemplateType;
use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Mail\Concerns\ResolvesTemplate;
use App\Models\Inventory\Product;
use App\Services\Settings\TenantSettings;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ProductAvailableMail extends BaseMailable
{
    use BakerBranded;
    use ResolvesTemplate;

    public function __construct(
        public Product $product,
        public string $customerName = '',
    ) {}

    public function envelope(): Envelope
    {
        $resolved = $this->resolveTemplate(EmailTemplateType::ProductAvailable, $this->placeholders());
        $storeName = app(TenantSettings::class)->store->name;

        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: $resolved['subject'] ?? "{$this->product->name} is back at {$storeName}!",
        );
    }

    public function content(): Content
    {
        $resolved = $this->resolveTemplate(EmailTemplateType::ProductAvailable, $this->placeholders());

        if ($resolved && $resolved['body']) {
            return new Content(
                view: 'emails.custom-template',
                with: ['customBody' => $resolved['body']],
            );
        }

        return new Content(
            html: 'emails.customers.product-available',
            with: [
                'product' => $this->product,
                'customerName' => $this->customerName,
            ],
        );
    }

    /**
     * @return array<string, string>
     */
    private function placeholders(): array
    {
        return [
            'customer_name' => $this->customerName ?: 'there',
            'product_name' => $this->product->name,
            'store_name' => app(TenantSettings::class)->store->name,
        ];
    }
}
