<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PurchaseOrder extends Mailable implements ShouldQueue
{
    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    use Queueable, SerializesModels;

    public function __construct(
        public string $supplierName,
        public string $storeName,
        public array $items,
        public float $total,
        public string $requestedDate,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Purchase Order from {$this->storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.purchase-order',
        );
    }
}
