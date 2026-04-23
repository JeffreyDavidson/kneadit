<?php

namespace App\Mail\Customers;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Financial\Coupon;
use App\Models\Orders\Cart;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\URL;

class AbandonedCartRecoveryMail extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public Cart $cart,
        public ?Coupon $coupon = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: 'You left something in your cart',
        );
    }

    public function content(): Content
    {
        $recoveryUrl = URL::temporarySignedRoute(
            'cart.recover',
            now()->addDays(7),
            ['cart_token' => $this->cart->cart_token],
        );

        return new Content(
            view: 'emails.customers.abandoned-cart-recovery',
            with: [
                'cart' => $this->cart,
                'coupon' => $this->coupon,
                'recoveryUrl' => $recoveryUrl,
            ],
        );
    }
}
