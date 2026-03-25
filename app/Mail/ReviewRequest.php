<?php

namespace App\Mail;

use App\Mail\Concerns\BakerBranded;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReviewRequest extends Mailable implements ShouldQueue
{
    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    use BakerBranded;
    use Queueable, SerializesModels;

    public Order $order;

    public string $storeName;

    public string $reviewUrl;

    /** @var Collection<int, OrderItem> */
    public Collection $orderItems;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->storeName = Setting::get('store_name', 'KneadIt Bakery');
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
