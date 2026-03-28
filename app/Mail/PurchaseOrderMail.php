<?php

namespace App\Mail;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PurchaseOrderMail extends BaseMailable
{
    /** @param array<int, array<string, mixed>> $items */
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
