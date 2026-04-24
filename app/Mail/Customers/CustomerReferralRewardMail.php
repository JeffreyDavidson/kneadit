<?php

namespace App\Mail\Customers;

use App\Enums\Marketing\EmailTemplateType;
use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Mail\Concerns\ResolvesTemplate;
use App\Models\Customers\CustomerReferral;
use App\Models\Financial\Coupon;
use App\Services\Settings\TenantSettings;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CustomerReferralRewardMail extends BaseMailable
{
    use BakerBranded;
    use ResolvesTemplate;

    public function __construct(
        public CustomerReferral $referral,
        public Coupon $coupon,
    ) {}

    public function envelope(): Envelope
    {
        $resolved = $this->resolveTemplate(EmailTemplateType::CustomerReferralReward, $this->placeholders());

        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: $resolved['subject'] ?? 'Thanks for the referral — here\'s your reward',
        );
    }

    public function content(): Content
    {
        $resolved = $this->resolveTemplate(EmailTemplateType::CustomerReferralReward, $this->placeholders());

        if ($resolved && $resolved['body']) {
            return new Content(
                view: 'emails.custom-template',
                with: ['customBody' => $resolved['body']],
            );
        }

        return new Content(
            view: 'emails.customers.referral-reward',
            with: [
                'referral' => $this->referral,
                'coupon' => $this->coupon,
            ],
        );
    }

    /**
     * @return array<string, string>
     */
    private function placeholders(): array
    {
        return [
            'customer_name' => $this->referral->referrer->name ?: 'there',
            'store_name' => app(TenantSettings::class)->store->name,
            'coupon_code' => $this->coupon->code,
            'coupon_amount' => (string) ($this->coupon->percentage ?? $this->coupon->fixed_amount ?? ''),
        ];
    }
}
