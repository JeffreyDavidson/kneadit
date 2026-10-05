<?php

namespace App\Actions\Customers;

use App\Enums\Financial\CouponType;
use App\Models\Customers\Customer;
use App\Models\Financial\Coupon;
use App\Services\Scheduling\BakeryClock;
use Illuminate\Support\Str;

/**
 * Gives a customer their single-use birthday coupon.
 *
 * Idempotent per customer and bakery-local year: the coupon records the
 * customer and year it was minted for, so repeated calls return it instead of
 * minting another. The code itself is random and carries no customer data.
 */
class CreateBirthdayCoupon
{
    public function __construct(
        private readonly BakeryClock $clock,
    ) {}

    public function __invoke(Customer $customer, int $discountPercent, int $validDays = 7): ?Coupon
    {
        if ($discountPercent <= 0) {
            return null;
        }

        $today = $this->clock->today();

        return Coupon::query()->firstOrCreate(
            ['customer_id' => $customer->id, 'birthday_year' => $today->year],
            fn (): array => [
                'code' => $this->mintCode(),
                'type' => CouponType::Percentage,
                'percentage' => $discountPercent,
                'max_uses' => 1,
                'used_count' => 0,
                'starts_at' => $today,
                'expires_at' => $today->copy()->addDays($validDays),
                'is_active' => true,
            ],
        );
    }

    private function mintCode(): string
    {
        do {
            $code = 'BDAY-'.Str::upper(Str::random(8));
        } while (Coupon::query()->where('code', $code)->exists());

        return $code;
    }
}
