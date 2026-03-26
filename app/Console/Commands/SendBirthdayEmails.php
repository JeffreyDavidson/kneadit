<?php

namespace App\Console\Commands;

use App\Mail\HappyBirthday;
use App\Models\Customer;
use App\Models\Tenant;
use App\Services\BirthdayService;
use App\Services\TenancyManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;

class SendBirthdayEmails extends Command
{
    protected $signature = 'birthday:send-emails';

    protected $description = 'Send happy birthday emails to customers with birthdays today';

    public function handle(TenancyManager $tenancyManager, BirthdayService $birthdayService): int
    {
        $tenants = Tenant::cursor();

        foreach ($tenants as $tenant) {
            try {
                $tenancyManager->withinTenant($tenant, function () use ($tenant, $birthdayService) {
                    if (settings('birthday_program_enabled', '1') !== '1') {
                        $this->info("Skipping {$tenant->id} — birthday program disabled");

                        return;
                    }

                    $this->processTenant($tenant, $birthdayService);
                });
            } catch (\Exception $e) {
                $this->error("Error processing {$tenant->id}: {$e->getMessage()}");
            }
        }

        return 0;
    }

    protected function processTenant(Tenant $tenant, BirthdayService $birthdayService): void
    {
        $today = Date::today();
        $discountPercent = (int) settings('birthday_discount_percentage', '15');
        $couponValidDays = (int) settings('birthday_coupon_valid_days', '7');

        $birthdayCustomers = Customer::query()->whereNotNull('birthday')
            ->whereMonth('birthday', $today->month)
            ->whereDay('birthday', $today->day)
            ->get();

        if ($birthdayCustomers->isEmpty()) {
            $this->info("[{$tenant->id}] No birthdays today.");

            return;
        }

        $sent = 0;

        foreach ($birthdayCustomers as $customer) {
            if (! $customer->email) {
                continue;
            }

            try {
                $coupon = $birthdayService->findOrCreateBirthdayCoupon($customer, $discountPercent, $couponValidDays);

                Mail::to($customer->email)->send(new HappyBirthday($customer, $coupon));
                $sent++;
                $this->info("✓ Sent birthday email to {$customer->name}");
            } catch (\Exception $e) {
                $this->error("✗ Failed for {$customer->name}: {$e->getMessage()}");
            }
        }

        $this->info("[{$tenant->id}] Sent {$sent} birthday emails.");
    }
}
