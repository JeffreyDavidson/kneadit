<?php

namespace App\Console\Commands\Customers;

use App\Enums\Financial\CouponType;
use App\Mail\Customers\AbandonedCartRecoveryMail;
use App\Models\Customers\Customer;
use App\Models\Financial\Coupon;
use App\Models\Orders\Cart;
use App\Models\Orders\Order;
use App\Models\Platform\Tenant;
use App\Services\Settings\TenantSettings;
use App\Services\Tenants\TenancyManager;
use App\ValueObjects\Money;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

#[Signature('carts:send-abandonment-emails')]
#[Description('Email customers who left items in their cart and did not check out')]
class SendAbandonedCartRecoveryCommand extends Command
{
    /** Carts untouched for longer than this are too stale to recover. */
    private const int MAX_CART_AGE_DAYS = 7;

    public function handle(TenancyManager $tenancyManager): int
    {
        $failures = $tenancyManager->forEachTenant(
            function (Tenant $tenant, TenantSettings $settings): void {
                $engagement = $settings->engagement;

                if ($tenant->is_paused || ! $engagement->abandonedCartRecoveryEnabled) {
                    return;
                }

                $cutoff = now()->subHours($engagement->abandonedCartRecoveryHours);

                Cart::query()
                    ->whereNotNull('recovery_claimed_at')
                    ->where('recovery_claimed_at', '<=', now()->subHour())
                    ->whereNull('recovery_sent_at')
                    ->update(['recovery_claimed_at' => null]);

                $carts = Cart::query()
                    ->forRecoverableCustomers()
                    ->whereNull('recovery_sent_at')
                    ->whereNull('recovery_claimed_at')
                    ->whereNull('converted_at')
                    ->where('last_activity_at', '<=', $cutoff)
                    ->activeSince(now()->subDays(self::MAX_CART_AGE_DAYS))
                    ->notExpired()
                    ->whereHas('items')
                    ->with('items.product')
                    ->get();

                $customersById = Customer::query()
                    ->whereIn('id', $carts->pluck('customer_id')->filter()->all())
                    ->get()
                    ->keyBy('id');
                $customersByEmail = Customer::query()
                    ->whereIn('email', $carts->pluck('customer_email')->filter()->all())
                    ->get()
                    ->keyBy('email');

                foreach ($carts as $cart) {
                    $customer = $cart->customer_id === null
                        ? $customersByEmail->get($cart->customer_email)
                        : $customersById->get($cart->customer_id);

                    if (! $customer instanceof Customer) {
                        continue;
                    }

                    if ($this->orderedSinceLastActivity($cart, $customer)) {
                        $cart->forceFill(['converted_at' => now()])->save();

                        continue;
                    }

                    $claimed = Cart::query()
                        ->whereKey($cart->getKey())
                        ->whereNull('recovery_sent_at')
                        ->whereNull('recovery_claimed_at')
                        ->update(['recovery_claimed_at' => now()]);

                    if ($claimed !== 1) {
                        continue;
                    }

                    $coupon = $engagement->abandonedCartRecoveryCouponDollars > 0
                        ? $this->mintCoupon($engagement->abandonedCartRecoveryCouponDollars)
                        : null;

                    try {
                        Mail::to($customer->email)->queue(new AbandonedCartRecoveryMail($cart, $customer, $coupon));

                        $cart->forceFill([
                            'recovery_sent_at' => now(),
                            'recovery_claimed_at' => null,
                        ])->save();
                    } catch (Throwable $exception) {
                        $cart->forceFill(['recovery_claimed_at' => null])->save();

                        throw $exception;
                    }
                }

                if ($carts->isNotEmpty()) {
                    $this->info("{$tenant->id}: queued {$carts->count()} recovery email(s)");
                }
            },
        );

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * The cart is only marked converted from the cookie on the device that
     * placed the order, so a customer who ordered elsewhere (or whose order the
     * bakery entered) still has an open cart. An order for the same email placed
     * after the cart was last touched means there is nothing to recover.
     */
    private function orderedSinceLastActivity(Cart $cart, Customer $customer): bool
    {
        return Order::query()
            ->placedByEmail($customer->email)
            ->where('created_at', '>', $cart->last_activity_at ?? $cart->created_at)
            ->exists();
    }

    private function mintCoupon(int $dollars): Coupon
    {
        do {
            $code = 'BACK-'.strtoupper(Str::random(5));
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
