<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CustomerReminder;
use App\Models\Setting;
use App\Mail\RepeatOrderReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendRepeatOrderReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:send-repeat-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send repeat order reminders to customers who haven\'t ordered recently';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if reminders are enabled
        $remindersEnabled = Setting::get('repeat_reminders_enabled', true);
        if (!$remindersEnabled) {
            $this->info('Repeat order reminders are disabled.');
            return 0;
        }

        $reminderDays = (int) Setting::get('repeat_reminder_days', 30);
        $cutoffDate = Carbon::today()->subDays($reminderDays);
        
        $this->info("Looking for customers who last ordered before {$cutoffDate->format('M j, Y')}...");
        
        // Find customers who need reminders
        $customersToRemind = $this->getCustomersNeedingReminders($cutoffDate);

        if ($customersToRemind->isEmpty()) {
            $this->info('No customers need repeat order reminders today.');
            return 0;
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($customersToRemind as $data) {
            $customer = $data['customer'];
            $lastOrderDate = $data['last_order_date'];
            $daysSinceLastOrder = $data['days_since_last_order'];
            
            if (!$customer->email) {
                $this->warn("Skipping customer {$customer->name} - no email address");
                $errorCount++;
                continue;
            }

            try {
                // Create or update reminder record
                CustomerReminder::updateOrCreate(
                    ['customer_id' => $customer->id],
                    [
                        'last_order_date' => $lastOrderDate,
                        'reminder_sent_at' => now(),
                        'next_reminder_date' => Carbon::today()->addDays($reminderDays), // Next reminder in 30 days
                    ]
                );

                // Send reminder email
                Mail::to($customer->email)->send(new RepeatOrderReminder($customer, $daysSinceLastOrder));
                
                $successCount++;
                $this->info("✓ Sent repeat order reminder to {$customer->name} (last order: {$lastOrderDate->format('M j, Y')})");
                
            } catch (\Exception $e) {
                $this->error("✗ Failed to send reminder to {$customer->name}: {$e->getMessage()}");
                $errorCount++;
            }
        }
        
        $this->info("\nRepeat order reminder summary:");
        $this->info("- Customers eligible: {$customersToRemind->count()}");
        $this->info("- Emails sent: {$successCount}");
        $this->info("- Errors: {$errorCount}");
        
        return $successCount > 0 ? 0 : 1;
    }

    private function getCustomersNeedingReminders($cutoffDate)
    {
        // Get customers who:
        // 1. Have placed at least one paid order
        // 2. Their last order was before the cutoff date
        // 3. Haven't been sent a reminder recently (or ever)
        
        $customers = Customer::whereHas('orders', function ($query) {
            $query->where('payment_status', 'paid');
        })
        ->with(['orders' => function ($query) {
            $query->where('payment_status', 'paid')
                  ->latest('requested_date');
        }])
        ->get()
        ->map(function ($customer) use ($cutoffDate) {
            $lastOrder = $customer->orders->first();
            
            if (!$lastOrder || $lastOrder->requested_date->isAfter($cutoffDate)) {
                return null; // Customer ordered recently
            }
            
            // Check if we've already sent a reminder recently
            $existingReminder = CustomerReminder::where('customer_id', $customer->id)->first();
            
            if ($existingReminder && $existingReminder->next_reminder_date && $existingReminder->next_reminder_date->isFuture()) {
                return null; // Already reminded recently
            }
            
            return [
                'customer' => $customer,
                'last_order_date' => $lastOrder->requested_date,
                'days_since_last_order' => $lastOrder->requested_date->diffInDays(Carbon::today()),
            ];
        })
        ->filter(); // Remove null entries
        
        return $customers;
    }
}