<?php

namespace App\Listeners\Customers;

use App\Enums\Financial\CouponType;
use App\Events\Customers\CustomerReferralCompleted;
use App\Listeners\SendEmailListener;
use App\Mail\Customers\CustomerReferralRewardMail;
use App\Models\Financial\Coupon;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\Money;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Str;

class SendCustomerReferralRewardEmailListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        /** @var CustomerReferralCompleted $event */
        return $event->referral->referrer->email;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var CustomerReferralCompleted $event */
        $referral = $event->referral;
        $referral->loadMissing(['referrer', 'referred']);

        $reward = (float) resolve(TenantSettings::class)->engagement->customerReferralDiscountDollars;
        $coupon = $this->mintRewardCoupon($referral->referrer_customer_id, $reward);

        $referral->forceFill(['reward_coupon_id' => $coupon->id])->save();

        return new CustomerReferralRewardMail($referral, $coupon);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var CustomerReferralCompleted $event */
        return ['referral' => $event->referral->id];
    }

    private function mintRewardCoupon(int $referrerId, float $rewardDollars): Coupon
    {
        do {
            $code = 'REF-' . strtoupper(Str::random(6));
        } while (Coupon::query()->where('code', $code)->exists());

        return Coupon::query()->create([
            'code' => $code,
            'type' => CouponType::Fixed,
            'fixed_amount' => Money::fromDollars($rewardDollars),
            'max_uses' => 1,
            'is_active' => true,
            'expires_at' => now()->addMonths(3),
        ]);
    }
}
