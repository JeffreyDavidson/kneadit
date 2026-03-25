<?php

namespace App\Mail;

use App\Mail\Concerns\BakerBranded;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductAvailable extends Mailable implements ShouldQueue
{
    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    use BakerBranded;
    use Queueable, SerializesModels;

    public function __construct(
        public Product $product,
        public string $customerName = '',
    ) {}

    public function envelope(): Envelope
    {
        $storeName = Setting::get('store_name', 'KneadIt Bakery');

        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: "{$this->product->name} is back at {$storeName}!",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.product-available',
            with: [
                'product' => $this->product,
                'customerName' => $this->customerName,
                'storeName' => Setting::get('store_name', 'KneadIt Bakery'),
            ],
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
