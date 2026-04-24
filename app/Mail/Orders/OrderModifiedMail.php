<?php

namespace App\Mail\Orders;

use App\Enums\Marketing\EmailTemplateType;
use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Mail\Concerns\ResolvesTemplate;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\Money;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderModifiedMail extends BaseMailable
{
    use BakerBranded;
    use ResolvesTemplate;

    public function __construct(
        public Order $order,
        public Money $previousSubtotal,
        public Money $previousTotal,
    ) {}

    public function envelope(): Envelope
    {
        $resolved = $this->resolveTemplate(EmailTemplateType::OrderModified, $this->placeholders());

        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: $resolved['subject'] ?? "Your order #{$this->order->order_number} was updated",
        );
    }

    public function content(): Content
    {
        $resolved = $this->resolveTemplate(EmailTemplateType::OrderModified, $this->placeholders());

        if ($resolved && $resolved['body']) {
            return new Content(
                view: 'emails.custom-template',
                with: ['customBody' => $resolved['body']],
            );
        }

        return new Content(
            view: 'emails.orders.order-modified',
            with: [
                'order' => $this->order,
                'previousSubtotal' => $this->previousSubtotal,
                'previousTotal' => $this->previousTotal,
            ],
        );
    }

    /**
     * @return array<string, string>
     */
    private function placeholders(): array
    {
        return [
            'customer_name' => $this->order->customer?->name ?: 'there',
            'store_name' => resolve(TenantSettings::class)->store->name,
            'order_number' => (string) $this->order->order_number,
            'previous_total' => $this->previousTotal->formatted(),
            'new_total' => $this->order->total->formatted(),
        ];
    }
}
