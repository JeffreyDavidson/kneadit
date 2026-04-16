<?php

namespace App\Mail\Customers;

use App\Enums\Marketing\EmailTemplateType;
use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Customers\Customer;
use App\Models\Financial\Coupon;
use App\Services\Email\EmailTemplateRenderer;
use App\Services\Settings\TenantSettings;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class HappyBirthdayMail extends BaseMailable
{
    use BakerBranded;

    public string $storeName;

    public function __construct(
        public Customer $customer,
        public ?Coupon $coupon = null,
    ) {
        $this->storeName = app(TenantSettings::class)->storeName;
    }

    public function envelope(): Envelope
    {
        $resolved = app(EmailTemplateRenderer::class)->resolve(
            EmailTemplateType::HappyBirthday,
            $this->placeholders(),
        );

        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: $resolved['subject'] ?? "🎂 Happy Birthday, {$this->customer->name}! A Sweet Gift From {$this->storeName}",
        );
    }

    public function content(): Content
    {
        $resolved = app(EmailTemplateRenderer::class)->resolve(
            EmailTemplateType::HappyBirthday,
            $this->placeholders(),
        );

        if ($resolved) {
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
            'coupon_code' => $this->coupon?->code ?? '',
            'coupon_amount' => $this->coupon ? '$' . number_format($this->coupon->value / 100, 2) : '',
            'store_name' => $this->storeName,
        ];
    }
}
