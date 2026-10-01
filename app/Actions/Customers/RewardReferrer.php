<?php

namespace App\Actions\Customers;

use App\Enums\Financial\CouponType;
use App\Models\Customers\CustomerReferral;
use App\Models\Financial\Coupon;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Gives the referrer of a completed referral their single-use reward coupon.
 *
 * Idempotent: once the referral has a reward coupon, later calls return it
 * instead of minting another, so repeated events and retried jobs are safe.
 */
class RewardReferrer
{
    public function __construct(
        private readonly TenantSettings $settings,
    ) {}

    public function __invoke(CustomerReferral $referral): Coupon
    {
        return DB::transaction(function () use ($referral): Coupon {
            $locked = CustomerReferral::query()
                ->with('rewardCoupon')
                ->lockForUpdate()
                ->findOrFail($referral->id);

            if ($locked->rewardCoupon instanceof Coupon) {
                return $locked->rewardCoupon;
            }

            $coupon = $this->mintCoupon();

            $locked->update(['reward_coupon_id' => $coupon->id]);

            return $coupon;
        });
    }

    private function mintCoupon(): Coupon
    {
        do {
            $code = 'REF-'.strtoupper(Str::random(6));
        } while (Coupon::query()->where('code', $code)->exists());

        return Coupon::query()->create([
            'code' => $code,
            'type' => CouponType::Fixed,
            'fixed_amount' => Money::fromDollars((float) $this->settings->engagement->customerReferralDiscountDollars),
            'max_uses' => 1,
            'is_active' => true,
            'expires_at' => now()->addMonths(3),
        ]);
    }
}
