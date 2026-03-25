<?php

namespace App\Console\Commands;

use App\Enums\CouponType;
use App\Mail\BirthdayDiscount;
use App\Models\Coupon;
use App\Models\CustomerProfile;
use App\Models\Setting;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;

class SendBirthdayDiscounts extends Command
{
    protected $signature = 'birthday:send-discounts';

    protected $description = 'Send birthday discount coupons to customers with birthdays today across all tenants';

    public function handle(): int
    {
        $tenants = Tenant::cursor();

        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);
            Setting::flushCache();

            try {
                if (Setting::get('birthday_program_enabled', '1') !== '1') {
                    continue;
                }

                $this->processTenant($tenant);
            } catch (\Exception $e) {
                $this->error("Error processing {$tenant->id}: {$e->getMessage()}");
            }
        }

        return Command::SUCCESS;
    }

    protected function processTenant(Tenant $tenant): void
    {
        $today = Date::today();
        $discountPercent = (int) Setting::get('birthday_discount_percent', 15);

        $birthdayCustomers = CustomerProfile::query()->whereMonth('birthday', $today->month)
            ->whereDay('birthday', $today->day)
            ->with('customer')
            ->get();

        if ($birthdayCustomers->isEmpty()) {
            return;
        }

        $this->info("Tenant {$tenant->store_name}: {$birthdayCustomers->count()} birthday(s)");

        foreach ($birthdayCustomers as $customerProfile) {
            $customer = $customerProfile->customer;

            if (! $customer || ! $customer->email) {
                continue;
            }

            try {
                $couponCode = 'BDAY-'.$customer->id.'-'.$today->year;

                if (Coupon::query()->where('code', $couponCode)->exists()) {
                    continue;
                }

                $coupon = Coupon::query()->create([
                    'code' => $couponCode,
                    'type' => CouponType::Percentage,
                    'value' => $discountPercent,
                    'max_uses' => 1,
                    'used_count' => 0,
                    'starts_at' => $today,
                    'expires_at' => $today->copy()->addDays(7),
                    'is_active' => true,
                ]);

                Mail::to($customer->email)->send(new BirthdayDiscount($customer, $coupon));
                $this->info("  ✓ {$customer->name}");
            } catch (\Exception $e) {
                $this->error("  ✗ {$customer->name}: {$e->getMessage()}");
            }
        }
    }
}
