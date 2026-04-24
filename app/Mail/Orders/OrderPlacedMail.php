<?php

namespace App\Mail\Orders;

use App\Enums\Marketing\EmailTemplateType;
use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Mail\Concerns\ResolvesTemplate;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderPlacedMail extends BaseMailable
{
    use BakerBranded;
    use ResolvesTemplate;

    public function __construct(
        public Order $order,
    ) {}

    public function envelope(): Envelope
    {
        $resolved = $this->resolveTemplate(EmailTemplateType::OrderPlaced, $this->placeholders());

        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: $resolved['subject'] ?? $this->defaultSubject(),
        );
    }

    public function content(): Content
    {
        $resolved = $this->resolveTemplate(EmailTemplateType::OrderPlaced, $this->placeholders());

        if ($resolved && $resolved['body']) {
            return new Content(
                view: 'emails.custom-template',
                with: ['customBody' => $resolved['body']],
            );
        }

        return new Content(
            view: 'emails.orders.order-placed',
            with: ['order' => $this->order],
        );
    }

    /**
     * @return array<string, string>
     */
    private function placeholders(): array
    {
        return [
            'customer_name' => $this->order->customer->name ?? 'there',
            'order_number' => $this->order->order_number,
            'order_total' => '$' . number_format($this->order->total->dollars() / 100, 2),
            'store_name' => resolve(TenantSettings::class)->store->name,
        ];
    }

    private function defaultSubject(): string
    {
        $number = $this->order->order_number;
        $storeName = resolve(TenantSettings::class)->store->name;

        return "Order #{$number} Received — {$storeName}";
    }
}
