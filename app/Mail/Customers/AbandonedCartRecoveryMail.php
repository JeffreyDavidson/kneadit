<?php

namespace App\Mail\Customers;

use App\Enums\Marketing\EmailTemplateType;
use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Mail\Concerns\ResolvesTemplate;
use App\Models\Financial\Coupon;
use App\Models\Orders\Cart;
use App\Services\Settings\TenantSettings;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\URL;

class AbandonedCartRecoveryMail extends BaseMailable
{
    use BakerBranded;
    use ResolvesTemplate;

    public function __construct(
        public Cart $cart,
        public ?Coupon $coupon = null,
    ) {}

    public function envelope(): Envelope
    {
        $resolved = $this->resolveTemplate(EmailTemplateType::AbandonedCartRecovery, $this->placeholders());

        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: $resolved['subject'] ?? 'You left something in your cart',
        );
    }

    public function content(): Content
    {
        $resolved = $this->resolveTemplate(EmailTemplateType::AbandonedCartRecovery, $this->placeholders());

        if ($resolved && $resolved['body']) {
            return new Content(
                view: 'emails.custom-template',
                with: ['customBody' => $resolved['body']],
            );
        }

        return new Content(
            view: 'emails.customers.abandoned-cart-recovery',
            with: [
                'cart' => $this->cart,
                'coupon' => $this->coupon,
                'recoveryUrl' => $this->recoveryUrl(),
            ],
        );
    }

    private function recoveryUrl(): string
    {
        return URL::temporarySignedRoute(
            'cart.recover',
            now()->addDays(7),
            ['cart_token' => $this->cart->cart_token],
        );
    }

    /**
     * @return array<string, string>
     */
    private function placeholders(): array
    {
        return [
            'customer_name' => $this->cart->customer_name ?: 'there',
            'store_name' => resolve(TenantSettings::class)->store->name,
            'recovery_url' => $this->recoveryUrl(),
            'coupon_code' => $this->coupon ? $this->coupon->code : '',
        ];
    }
}
