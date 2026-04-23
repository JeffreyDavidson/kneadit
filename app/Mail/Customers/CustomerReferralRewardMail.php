<?php

namespace App\Mail\Customers;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Customers\CustomerReferral;
use App\Models\Financial\Coupon;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CustomerReferralRewardMail extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public CustomerReferral $referral,
        public Coupon $coupon,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: 'Thanks for the referral — here\'s your reward',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customers.referral-reward',
            with: [
                'referral' => $this->referral,
                'coupon' => $this->coupon,
            ],
        );
    }
}
