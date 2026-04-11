<?php

namespace App\Services\Engagement\Engagements;

use App\Contracts\Engagement\CustomerEngagement;
use App\Contracts\Engagement\EngagementRecipient;
use App\Enums\Orders\PaymentStatus;
use App\Events\Customers\RepeatOrderReminderDue;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerReminder;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

class RepeatOrderReminderEngagement implements CustomerEngagement
{
    public function isEnabled(TenantSettings $settings): bool
    {
        return $settings->engagement->repeatRemindersEnabled;
    }

    /** @return Collection<int, EngagementRecipient> */
    public function findRecipients(TenantSettings $settings): Collection
    {
        $reminderDays = $settings->engagement->repeatReminderDays;
        $cutoffDate = Date::today()->subDays($reminderDays);

        return Customer::query()
            ->where('email', '!=', '')
            ->whereHas('orders', fn (Builder $q) => $q->where('payment_status', PaymentStatus::Paid))
            ->with([
                'orders' => fn (Builder $q) => $q->where('payment_status', PaymentStatus::Paid)->latest('delivery_date'),
                'customerReminders',
            ])
            ->get()
            ->map(function (Customer $customer) use ($cutoffDate, $reminderDays) {
                $lastOrder = $customer->orders->first();

                if (! $lastOrder || $lastOrder->delivery_date?->isAfter($cutoffDate)) {
                    return null;
                }

                $existingReminder = $customer->customerReminders->first();
                if ($existingReminder && $existingReminder->next_reminder_date?->isFuture()) {
                    return null;
                }

                return new EngagementRecipient(
                    email: $customer->email,
                    name: $customer->name,
                    model: $customer,
                    context: [
                        'last_order_date' => $lastOrder->delivery_date,
                        'days_since_last_order' => $lastOrder->delivery_date?->diffInDays(Date::today()),
                        'reminder_days' => $reminderDays,
                    ],
                );
            })
            ->filter();
    }

    public function dispatchForRecipient(EngagementRecipient $recipient, TenantSettings $settings): void
    {
        /** @var Customer $customer */
        $customer = $recipient->model;
        $reminderDays = $recipient->context['reminder_days'];

        CustomerReminder::query()->updateOrCreate(
            ['customer_id' => $customer->id],
            [
                'last_order_date' => $recipient->context['last_order_date'],
                'reminder_sent_at' => now(),
                'next_reminder_date' => Date::today()->addDays($reminderDays),
            ],
        );

        RepeatOrderReminderDue::dispatch($customer, $recipient->context['days_since_last_order']);
    }
}
