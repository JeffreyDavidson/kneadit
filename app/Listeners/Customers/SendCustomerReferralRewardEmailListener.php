<?php

namespace App\Listeners\Customers;

use App\Actions\Customers\RewardReferrer;
use App\Events\Customers\CustomerReferralCompleted;
use App\Listeners\SendEmailListener;
use App\Mail\Customers\CustomerReferralRewardMail;
use Illuminate\Contracts\Mail\Mailable;

class SendCustomerReferralRewardEmailListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        /** @var CustomerReferralCompleted $event */
        return $event->referral->referrer->email;
    }

    /**
     * The reward coupon is created at most once per referral by RewardReferrer,
     * so a repeated event or a retried job mails the same coupon.
     */
    protected function getMailable(object $event): Mailable
    {
        /** @var CustomerReferralCompleted $event */
        $referral = $event->referral;
        $referral->loadMissing(['referrer', 'referred']);

        return new CustomerReferralRewardMail($referral, resolve(RewardReferrer::class)($referral));
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var CustomerReferralCompleted $event */
        return ['referral' => $event->referral->id];
    }
}
