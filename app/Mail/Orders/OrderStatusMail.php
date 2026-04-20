<?php

namespace App\Mail\Orders;

use App\Enums\Marketing\EmailTemplateType;
use App\Enums\Orders\OrderStatus;
use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Mail\Concerns\ResolvesTemplate;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderStatusMail extends BaseMailable
{
    use BakerBranded;
    use ResolvesTemplate;

    public function __construct(
        public Order $order,
        public OrderStatus $status,
    ) {}

    public function envelope(): Envelope
    {
        $resolved = $this->resolveTemplate($this->templateType(), $this->placeholders());

        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: $resolved['subject'] ?? $this->resolveDefaultSubject(),
        );
    }

    public function content(): Content
    {
        $resolved = $this->resolveTemplate($this->templateType(), $this->placeholders());

        if ($resolved && $resolved['body']) {
            return new Content(
                view: 'emails.custom-template',
                with: ['customBody' => $resolved['body']],
            );
        }

        return new Content(
            html: "emails.orders.order-{$this->status->value}",
            with: [
                'order' => $this->order,
                'customer' => $this->order->customer,
                'orderItems' => $this->order->orderItems,
            ],
        );
    }

    private function templateType(): ?EmailTemplateType
    {
        return match ($this->status) {
            OrderStatus::Confirmed => EmailTemplateType::OrderConfirmed,
            OrderStatus::Baking => EmailTemplateType::OrderBaking,
            OrderStatus::Ready => EmailTemplateType::OrderReady,
            OrderStatus::Delivered => EmailTemplateType::OrderDelivered,
            OrderStatus::Cancelled => EmailTemplateType::OrderCancelled,
            default => null,
        };
    }

    /**
     * @return array<string, string>
     */
    private function placeholders(): array
    {
        $settings = app(TenantSettings::class);

        return [
            'customer_name' => $this->order->customer->name ?? 'there',
            'order_number' => $this->order->order_number,
            'order_total' => '$' . number_format($this->order->total->dollars() / 100, 2),
            'delivery_date' => $this->order->delivery_date?->format('M j, Y') ?? '',
            'store_name' => $settings->storeName,
        ];
    }

    private function resolveDefaultSubject(): string
    {
        $number = $this->order->order_number;
        $storeName = app(TenantSettings::class)->storeName;

        return match ($this->status) {
            OrderStatus::Confirmed => "Order #{$number} Confirmed — {$storeName}",
            OrderStatus::Baking => "Your Order #{$number} is Being Prepared — {$storeName}",
            OrderStatus::Ready => "Order #{$number} is Ready! — {$storeName}",
            OrderStatus::Delivered => "Order #{$number} Delivered — {$storeName}",
            OrderStatus::Cancelled => "Order #{$number} Cancelled — {$storeName}",
            default => "Order #{$number} Update — {$storeName}",
        };
    }
}
