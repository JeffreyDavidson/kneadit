<?php

namespace App\Mail\Customers;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Customers\ContactMessage;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactMessageReplyMail extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public ContactMessage $contactMessage,
        public string $replyBody,
        public string $replySubject,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: $this->replySubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customers.contact-reply',
            with: [
                'replyBody' => $this->replyBody,
                'originalSubject' => $this->contactMessage->subject,
                'originalMessage' => $this->contactMessage->message,
                'originalSentAt' => $this->contactMessage->created_at,
                'customerName' => $this->contactMessage->name,
            ],
        );
    }
}
