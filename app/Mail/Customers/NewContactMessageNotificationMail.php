<?php

namespace App\Mail\Customers;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Customers\ContactMessage;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewContactMessageNotificationMail extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public ContactMessage $message,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->message->subject
            ? "New contact message: {$this->message->subject}"
            : "New contact message from {$this->message->name}";

        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: $this->message->email ? [new Address($this->message->email, $this->message->name)] : [],
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customers.new-contact-message-notification',
            with: [
                'contactMessage' => $this->message,
            ],
        );
    }
}
