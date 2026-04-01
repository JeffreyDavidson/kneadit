<?php

namespace App\Mail\Customers;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Coupon;
use App\Models\Customer;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class BirthdayDiscountMail extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public Customer $customer,
        public Coupon $coupon,
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: '🎂 Happy Birthday! Your Special Treat Awaits!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.birthday-discount',
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
