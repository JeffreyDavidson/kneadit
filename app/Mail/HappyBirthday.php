<?php

namespace App\Mail;

use App\Mail\Concerns\BakerBranded;
use App\Models\Coupon;
use App\Models\Customer;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class HappyBirthday extends BaseMailable
{
    use BakerBranded;

    public string $storeName;

    public function __construct(
        public Customer $customer,
        public ?Coupon $coupon = null,
    ) {
        $this->storeName = settings('store_name', 'Our Bakery');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: "🎂 Happy Birthday, {$this->customer->name}! A Sweet Gift From {$this->storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.happy-birthday',
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
