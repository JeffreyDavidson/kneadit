<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Mail\RepeatOrderReminder;
use App\Models\Customer;
use App\Models\CustomerReminder;
use App\Models\Setting;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendRepeatOrderReminders extends Command
{
    protected $signature = 'orders:send-repeat-reminders';

    protected $description = 'Send repeat order reminders to customers across all tenants';

    public function handle()
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);
            Setting::flushCache();

            try {
                if (Setting::get('repeat_reminders_enabled', true) != true) {
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
        $reminderDays = (int) Setting::get('repeat_reminder_days', 30);
        $cutoffDate = Carbon::today()->subDays($reminderDays);

        $customersToRemind = $this->getCustomersNeedingReminders($cutoffDate, $reminderDays);

        if ($customersToRemind->isEmpty()) {
            return;
        }

        $this->info("Tenant {$tenant->store_name}: {$customersToRemind->count()} reminder(s)");

        foreach ($customersToRemind as $data) {
            $customer = $data['customer'];

            if (! $customer->email) {
                continue;
            }

            try {
                CustomerReminder::updateOrCreate(
                    ['customer_id' => $customer->id],
                    [
                        'last_order_date' => $data['last_order_date'],
                        'reminder_sent_at' => now(),
                        'next_reminder_date' => Carbon::today()->addDays($reminderDays),
                    ]
                );

                Mail::to($customer->email)->send(new RepeatOrderReminder($customer, $data['days_since_last_order']));
                $this->info("  ✓ {$customer->name}");
            } catch (\Exception $e) {
                $this->error("  ✗ {$customer->name}: {$e->getMessage()}");
            }
        }
    }

    private function getCustomersNeedingReminders($cutoffDate, $reminderDays)
    {
        return Customer::whereHas('orders', fn ($q) => $q->where('payment_status', PaymentStatus::Paid))
            ->with(['orders' => fn ($q) => $q->where('payment_status', PaymentStatus::Paid)->latest('delivery_date')])
            ->get()
            ->map(function ($customer) use ($cutoffDate) {
                $lastOrder = $customer->orders->first();

                if (! $lastOrder || $lastOrder->delivery_date->isAfter($cutoffDate)) {
                    return null;
                }

                $existingReminder = CustomerReminder::where('customer_id', $customer->id)->first();
                if ($existingReminder && $existingReminder->next_reminder_date?->isFuture()) {
                    return null;
                }

                return [
                    'customer' => $customer,
                    'last_order_date' => $lastOrder->delivery_date,
                    'days_since_last_order' => $lastOrder->delivery_date->diffInDays(Carbon::today()),
                ];
            })
            ->filter();
    }
}
