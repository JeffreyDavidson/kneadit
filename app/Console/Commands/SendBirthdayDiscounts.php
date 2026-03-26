<?php

namespace App\Console\Commands;

use App\Mail\BirthdayDiscount;
use App\Models\CustomerProfile;
use App\Models\Tenant;
use App\Services\Customer\BirthdayService;
use App\Services\Tenant\TenancyManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBirthdayDiscounts extends Command
{
    protected $signature = 'birthday:send-discounts';

    protected $description = 'Send birthday discount coupons to customers with birthdays today across all tenants';

    public function handle(TenancyManager $tenancyManager, BirthdayService $birthdayService): int
    {
        $tenants = Tenant::cursor();
        $failures = 0;

        foreach ($tenants as $tenant) {
            try {
                $tenancyManager->withinTenant($tenant, function () use ($tenant, $birthdayService) {
                    if (settings('birthday_program_enabled', '1') !== '1') {
                        return;
                    }

                    $this->processTenant($tenant, $birthdayService);
                });
            } catch (\Exception $e) {
                $this->error("Error processing {$tenant->id}: {$e->getMessage()}");
                Log::warning('Birthday discount processing failed', ['tenant' => $tenant->id, 'error' => $e->getMessage()]);
                $failures++;
            }
        }

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function processTenant(Tenant $tenant, BirthdayService $birthdayService): void
    {
        $today = Date::today();
        $discountPercent = (int) settings('birthday_discount_percent', 15);

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
                $coupon = $birthdayService->findOrCreateBirthdayCoupon($customer, $discountPercent);
                if (! $coupon) {
                    continue;
                }

                Mail::to($customer->email)->send(new BirthdayDiscount($customer, $coupon));
                $this->info("  ✓ {$customer->name}");
            } catch (\Exception $e) {
                $this->error("  ✗ {$customer->name}: {$e->getMessage()}");
                Log::warning('Birthday discount send failed', ['customer' => $customer->name, 'error' => $e->getMessage()]);
            }
        }
    }
}
