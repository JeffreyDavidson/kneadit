<?php

namespace App\Mail\Customers;

use App\Enums\Marketing\EmailTemplateType;
use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Services\Email\EmailTemplateRenderer;
use App\Services\Settings\TenantSettings;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ReviewRequestMail extends BaseMailable
{
    use BakerBranded;

    public Order $order;

    public string $storeName;

    public string $reviewUrl;

    /** @var Collection<int, OrderItem> */
    public Collection $orderItems;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->storeName = app(TenantSettings::class)->storeName;
        $this->reviewUrl = url("/review/{$order->id}");
        $this->orderItems = $order->orderItems()->with('product')->get();
    }

    public function envelope(): Envelope
    {
        $resolved = app(EmailTemplateRenderer::class)->resolve(
            EmailTemplateType::ReviewRequest,
            $this->placeholders(),
        );

        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: $resolved['subject'] ?? "How was your order from {$this->storeName}? ⭐",
        );
    }

    public function content(): Content
    {
        $resolved = app(EmailTemplateRenderer::class)->resolve(
            EmailTemplateType::ReviewRequest,
            $this->placeholders(),
        );

        if ($resolved && $resolved['body']) {
            return new Content(
                view: 'emails.custom-template',
                with: ['customBody' => $resolved['body']],
            );
        }

        return new Content(
            html: 'emails.customers.review-request',
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
            'review_url' => $this->reviewUrl,
            'store_name' => $this->storeName,
        ];
    }
}
