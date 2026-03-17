<?php

namespace App\Mail;

use App\Mail\Concerns\BakerBranded;
use App\Models\Coupon;
use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BirthdayDiscount extends Mailable implements ShouldQueue
{
    use BakerBranded;
    use Queueable, SerializesModels;

    public function __construct(
        public Customer $customer,
        public Coupon $coupon
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

    public function attachments(): array
    {
        return [];
    }
}
