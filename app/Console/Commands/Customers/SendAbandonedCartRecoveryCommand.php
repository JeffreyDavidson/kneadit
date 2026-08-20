<?php

namespace App\Console\Commands\Customers;

use App\Enums\Financial\CouponType;
use App\Mail\Customers\AbandonedCartRecoveryMail;
use App\Models\Financial\Coupon;
use App\Models\Orders\Cart;
use App\Models\Platform\Tenant;
use App\Services\Settings\TenantSettings;
use App\Services\Tenants\TenancyManager;
use App\ValueObjects\Money;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

#[Signature('carts:send-abandonment-emails')]
#[Description('Email customers who left items in their cart and did not check out')]
class SendAbandonedCartRecoveryCommand extends Command
{
    public function handle(TenancyManager $tenancyManager): int
    {
        $failures = $tenancyManager->forEachTenant(
            function (Tenant $tenant, TenantSettings $settings): void {
                $engagement = $settings->engagement;

                if (! $engagement->abandonedCartRecoveryEnabled) {
                    return;
                }

                $cutoff = now()->subHours($engagement->abandonedCartRecoveryHours);

                $carts = Cart::query()->abandonedBefore($cutoff)->withRecoverableItems()->get();

                foreach ($carts as $cart) {
                    $coupon = $engagement->abandonedCartRecoveryCouponDollars > 0
                        ? $this->mintCoupon($engagement->abandonedCartRecoveryCouponDollars)
                        : null;

                    Mail::to($cart->customer_email)->queue(new AbandonedCartRecoveryMail($cart, $coupon));

                    $cart->forceFill(['recovery_sent_at' => now()])->save();
                }

                if ($carts->isNotEmpty()) {
                    $this->info("{$tenant->id}: queued {$carts->count()} recovery email(s)");
                }
            },
        );

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function mintCoupon(int $dollars): Coupon
    {
        do {
            $code = 'BACK-' . strtoupper(Str::random(5));
        } while (Coupon::query()->where('code', $code)->exists());

        return Coupon::query()->create([
            'code' => $code,
            'type' => CouponType::Fixed,
            'fixed_amount' => Money::fromDollars((float) $dollars),
            'max_uses' => 1,
            'is_active' => true,
            'expires_at' => now()->addDays(14),
        ]);
    }
}
