<?php

namespace App\Console\Commands;

use App\Enums\CouponType;
use App\Mail\HappyBirthday;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Setting;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;

class SendBirthdayEmails extends Command
{
    protected $signature = 'birthday:send-emails';

    protected $description = 'Send happy birthday emails to customers with birthdays today';

    public function handle(): int
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);

            try {
                if (Setting::get('birthday_program_enabled', '1') !== '1') {
                    $this->info("Skipping {$tenant->id} — birthday program disabled");

                    continue;
                }

                $this->processTenant($tenant);
            } catch (\Exception $e) {
                $this->error("Error processing {$tenant->id}: {$e->getMessage()}");
            }
        }

        return 0;
    }

    protected function processTenant($tenant): void
    {
        $today = Date::today();
        $discountPercent = (int) Setting::get('birthday_discount_percentage', '15');
        $couponValidDays = (int) Setting::get('birthday_coupon_valid_days', '7');

        $birthdayCustomers = Customer::whereNotNull('birthday')
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
                $coupon = null;

                if ($discountPercent > 0) {
                    $couponCode = 'BDAY-'.$customer->id.'-'.$today->year;
                    $existing = Coupon::where('code', $couponCode)->first();

                    if (! $existing) {
                        $coupon = Coupon::create([
                            'code' => $couponCode,
                            'type' => CouponType::Percentage,
                            'value' => $discountPercent,
                            'max_uses' => 1,
                            'used_count' => 0,
                            'starts_at' => $today,
                            'expires_at' => $today->copy()->addDays($couponValidDays),
                            'is_active' => true,
                        ]);
                    } else {
                        $coupon = $existing;
                    }
                }

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
