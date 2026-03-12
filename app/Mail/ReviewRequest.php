<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use App\Mail\Concerns\BakerBranded;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReviewRequest extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    use BakerBranded;

    public Order $order;

    public string $storeName;

    public string $reviewUrl;

    public $orderItems;

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

    public function attachments(): array
    {
        return [];
    }
}
