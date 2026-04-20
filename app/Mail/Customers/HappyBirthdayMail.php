<?php

namespace App\Mail\Customers;

use App\Enums\Marketing\EmailTemplateType;
use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Mail\Concerns\ResolvesTemplate;
use App\Models\Customers\Customer;
use App\Models\Financial\Coupon;
use App\Services\Settings\TenantSettings;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class HappyBirthdayMail extends BaseMailable
{
    use BakerBranded;
    use ResolvesTemplate;

    public string $storeName;

    public function __construct(
        public Customer $customer,
        public ?Coupon $coupon = null,
    ) {
        $this->storeName = app(TenantSettings::class)->store->name;
    }

    public function envelope(): Envelope
    {
        $resolved = $this->resolveTemplate(EmailTemplateType::HappyBirthday, $this->placeholders());

        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: $resolved['subject'] ?? "🎂 Happy Birthday, {$this->customer->name}! A Sweet Gift From {$this->storeName}",
        );
    }

    public function content(): Content
    {
        $resolved = $this->resolveTemplate(EmailTemplateType::HappyBirthday, $this->placeholders());

        if ($resolved && $resolved['body']) {
            return new Content(
                view: 'emails.custom-template',
                with: ['customBody' => $resolved['body']],
            );
        }

        return new Content(
            view: 'emails.customers.happy-birthday',
        );
    }

    /**
     * @return array<string, string>
     */
    private function placeholders(): array
    {
        return [
            'customer_name' => $this->customer->name,
            'coupon_code' => $this->coupon ? $this->coupon->code : '',
            'coupon_amount' => $this->coupon ? (string) ($this->coupon->percentage ?? $this->coupon->fixed_amount ?? '') : '',
            'store_name' => $this->storeName,
        ];
    }
}
