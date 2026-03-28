<?php

namespace App\Console\Commands;

use App\Mail\HappyBirthdayMail;
use App\Models\Customer;
use App\Models\Tenant;
use App\Services\Customer\BirthdayService;
use App\Services\Tenant\TenancyManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBirthdayEmails extends Command
{
    protected $signature = 'birthday:send-emails';

    protected $description = 'Send happy birthday emails to customers with birthdays today';

    public function handle(TenancyManager $tenancyManager, BirthdayService $birthdayService): int
    {
        $tenants = Tenant::cursor();
        $failures = 0;

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
                Log::warning('Birthday email processing failed', ['tenant' => $tenant->id, 'error' => $e->getMessage()]);
                $failures++;
            }
        }

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
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

                Mail::to($customer->email)->send(new HappyBirthdayMail($customer, $coupon));
                $sent++;
                $this->info("✓ Sent birthday email to {$customer->name}");
            } catch (\Exception $e) {
                $this->error("✗ Failed for {$customer->name}: {$e->getMessage()}");
                Log::warning('Birthday email send failed', ['customer' => $customer->name, 'error' => $e->getMessage()]);
            }
        }

        $this->info("[{$tenant->id}] Sent {$sent} birthday emails.");
    }
}
