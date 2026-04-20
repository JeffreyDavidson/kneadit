<?php

namespace App\Mail\Customers;

use App\Enums\Marketing\EmailTemplateType;
use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Mail\Concerns\ResolvesTemplate;
use App\Models\Customers\Customer;
use App\Services\Settings\TenantSettings;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RepeatOrderReminderMail extends BaseMailable
{
    use BakerBranded;
    use ResolvesTemplate;

    public function __construct(
        public Customer $customer,
        public int $daysSinceLastOrder,
    ) {}

    public function envelope(): Envelope
    {
        $resolved = $this->resolveTemplate(EmailTemplateType::RepeatOrderReminder, $this->placeholders());

        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: $resolved['subject'] ?? 'We Miss You! 🥖 Your Favorite Treats Are Waiting',
        );
    }

    public function content(): Content
    {
        $resolved = $this->resolveTemplate(EmailTemplateType::RepeatOrderReminder, $this->placeholders());

        if ($resolved && $resolved['body']) {
            return new Content(
                view: 'emails.custom-template',
                with: ['customBody' => $resolved['body']],
            );
        }

        return new Content(
            view: 'emails.customers.repeat-order-reminder',
        );
    }

    /**
     * @return array<string, string>
     */
    private function placeholders(): array
    {
        return [
            'customer_name' => $this->customer->name,
            'days_since_last_order' => (string) $this->daysSinceLastOrder,
            'store_name' => app(TenantSettings::class)->store->name,
        ];
    }
}
