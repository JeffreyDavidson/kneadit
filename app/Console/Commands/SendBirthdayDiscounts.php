<?php

namespace App\Console\Commands;

use App\Models\CustomerProfile;
use App\Models\Coupon;
use App\Models\Setting;
use App\Mail\BirthdayDiscount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendBirthdayDiscounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'birthday:send-discounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send birthday discount coupons to customers with birthdays today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $discountPercent = (int) Setting::get('birthday_discount_percent', 15);
        
        $this->info("Checking for birthday customers on {$today->format('M j, Y')}...");
        
        // Find customers with birthdays today
        $birthdayCustomers = CustomerProfile::whereMonth('birthday', $today->month)
            ->whereDay('birthday', $today->day)
            ->with('customer')
            ->get();

        if ($birthdayCustomers->isEmpty()) {
            $this->info('No birthday customers found today.');
            return;
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($birthdayCustomers as $customerProfile) {
            $customer = $customerProfile->customer;
            
            if (!$customer || !$customer->email) {
                $this->warn("Skipping customer ID {$customerProfile->customer_id} - no email address");
                $errorCount++;
                continue;
            }

            try {
                $couponCode = 'BDAY-' . $customer->id . '-' . $today->year;
                
                // Check if coupon already exists for this year
                $existingCoupon = Coupon::where('code', $couponCode)->first();
                
                if ($existingCoupon) {
                    $this->info("Birthday coupon already exists for {$customer->name} ({$couponCode})");
                    continue;
                }

                // Create birthday coupon
                $coupon = Coupon::create([
                    'code' => $couponCode,
                    'type' => 'percentage',
                    'value' => $discountPercent,
                    'max_uses' => 1,
                    'used_count' => 0,
                    'starts_at' => $today,
                    'expires_at' => $today->copy()->addDays(7), // Valid for 7 days
                    'is_active' => true,
                ]);

                // Send birthday email
                Mail::to($customer->email)->send(new BirthdayDiscount($customer, $coupon));
                
                $successCount++;
                $this->info("✓ Sent birthday discount to {$customer->name} ({$customer->email})");
                
            } catch (\Exception $e) {
                $this->error("✗ Failed to send birthday discount to {$customer->name}: {$e->getMessage()}");
                $errorCount++;
            }
        }
        
        $this->info("\nBirthday discount summary:");
        $this->info("- Customers found: {$birthdayCustomers->count()}");
        $this->info("- Emails sent: {$successCount}");
        $this->info("- Errors: {$errorCount}");
        
        return $successCount > 0 ? 0 : 1;
    }
}