<?php

namespace App\Mail;

use App\Mail\Concerns\BakerBranded;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailables\Attachment;
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
        $this->storeName = settings('store_name', 'KneadIt Bakery');
        $this->reviewUrl = url("/review/{$order->id}");
        $this->orderItems = $order->orderItems()->with('product')->get();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: "How was your order from {$this->storeName}? ⭐",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.review-request',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
