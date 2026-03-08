<?php

namespace App\Mail;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductAvailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Product $product,
        public string $customerName = '',
    ) {}

    public function envelope(): Envelope
    {
        $storeName = Setting::get('store_name', 'KneadIt Bakery');
        $replyTo = Setting::get('store_email');

        $envelope = new Envelope(
            subject: "{$this->product->name} is back at {$storeName}!",
        );

        if ($replyTo) {
            $envelope->replyTo($replyTo);
        }

        return $envelope;
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

    public function attachments(): array
    {
        return [];
    }
}
