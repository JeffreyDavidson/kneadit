<?php

namespace App\Mail\Customers;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Inventory\ProductWaitlist;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewWaitlistJoinNotificationMail extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public ProductWaitlist $entry,
    ) {}

    public function envelope(): Envelope
    {
        $product = $this->entry->product->name ?? 'a product';

        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: $this->entry->customer_email
                ? [new Address($this->entry->customer_email, $this->entry->customer_name ?? $this->entry->customer_email)]
                : [],
            subject: "New waitlist signup for {$product}",
        );
    }

    public function content(): Content
    {
        $this->entry->loadMissing('product');

        return new Content(
            view: 'emails.customers.new-waitlist-join-notification',
            with: [
                'entry' => $this->entry,
                'product' => $this->entry->product,
                'totalWaiting' => $this->entry->product
                    ? ProductWaitlist::query()
                        ->where('product_id', $this->entry->product_id)
                        ->whereNull('notified_at')
                        ->count()
                    : 1,
            ],
        );
    }
}
