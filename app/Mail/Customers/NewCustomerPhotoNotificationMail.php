<?php

namespace App\Mail\Customers;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Customers\CustomerPhoto;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewCustomerPhotoNotificationMail extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public CustomerPhoto $photo,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: $this->photo->customer_email
                ? [new Address($this->photo->customer_email, $this->photo->customer_name)]
                : [],
            subject: "📸 New gallery photo from {$this->photo->customer_name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customers.new-customer-photo-notification',
            with: [
                'photo' => $this->photo,
            ],
        );
    }
}
