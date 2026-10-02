<?php

declare(strict_types=1);

namespace App\Mail\Customers;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Customers\Customer;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Transactional order update sent by the Customers "Send message" bulk action
 * (for example "your pickup window changed"). Unlike BulkCustomerMessageMail
 * it is not marketing: it carries no unsubscribe link or headers and reaches
 * customers who opted out of marketing email.
 */
class OrderUpdateMessageMail extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public Customer $customer,
        public string $messageSubject,
        public string $body,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: $this->messageSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customers.bulk-customer-message',
            with: [
                'customer' => $this->customer,
                'body' => $this->body,
            ],
        );
    }
}
