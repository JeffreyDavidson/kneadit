<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Mail\RepeatOrderReminderMail;
use App\Models\Customer;
use App\Models\CustomerReminder;
use App\Models\Tenant;
use App\Services\Tenant\TenancyManager;
use Illuminate\Console\Command;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendRepeatOrderRemindersCommand extends Command
{
    protected $signature = 'orders:send-repeat-reminders';

    protected $description = 'Send repeat order reminders to customers across all tenants';

    public function handle(TenancyManager $tenancyManager): int
    {
        $tenants = Tenant::query()->cursor();
        $failures = 0;

        foreach ($tenants as $tenant) {
            try {
                $tenancyManager->withinTenant($tenant, function () use ($tenant) {
                    if (settings('repeat_reminders_enabled', true) != true) {
                        return;
                    }

                    $this->processTenant($tenant);
                });
            } catch (\Exception $e) {
                $this->error("Error processing {$tenant->id}: {$e->getMessage()}");
                Log::warning('Repeat order reminder processing failed', ['tenant' => $tenant->id, 'error' => $e->getMessage()]);
                $failures++;
            }
        }

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function processTenant(Tenant $tenant): void
    {
        $reminderDays = (int) settings('repeat_reminder_days', 30);
        $cutoffDate = Date::today()->subDays($reminderDays);

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
                CustomerReminder::query()->updateOrCreate(['customer_id' => $customer->id], [
                    'last_order_date' => $data['last_order_date'],
                    'reminder_sent_at' => now(),
                    'next_reminder_date' => Date::today()->addDays($reminderDays),
                ]);

                Mail::to($customer->email)->send(new RepeatOrderReminderMail($customer, $data['days_since_last_order']));
                $this->info("  ✓ {$customer->name}");
            } catch (\Exception $e) {
                $this->error("  ✗ {$customer->name}: {$e->getMessage()}");
                Log::warning('Repeat order reminder send failed', ['customer' => $customer->name, 'error' => $e->getMessage()]);
            }
        }
    }

    /** @return Collection<int, mixed> */
    private function getCustomersNeedingReminders(Carbon $cutoffDate, int $reminderDays): Collection
    {
        return Customer::query()->whereHas('orders', fn (Builder $q) => $q->where('payment_status', PaymentStatus::Paid))
            ->with([
                'orders' => fn (EloquentBuilder $q) => $q->where('payment_status', PaymentStatus::Paid)->latest('delivery_date'),
                'customerReminders',
            ])
            ->get()
            ->map(function (Customer $customer) use ($cutoffDate) {
                $lastOrder = $customer->orders->first();

                if (! $lastOrder || $lastOrder->delivery_date?->isAfter($cutoffDate)) {
                    return null;
                }

                $existingReminder = $customer->customerReminders->first();
                if ($existingReminder && $existingReminder->next_reminder_date?->isFuture()) {
                    return null;
                }

                return [
                    'customer' => $customer,
                    'last_order_date' => $lastOrder->delivery_date,
                    'days_since_last_order' => $lastOrder->delivery_date?->diffInDays(Date::today()),
                ];
            })
            ->filter();
    }
}
