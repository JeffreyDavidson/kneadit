<?php

namespace App\Services\Engagement\Engagements;

use App\Events\Customers\ReviewRequested;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Services\Engagement\Contracts\CustomerEngagement;
use App\Services\Engagement\Contracts\EngagementRecipient;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Collection;

class ReviewRequestEngagement implements CustomerEngagement
{
    public function isEnabled(TenantSettings $settings): bool
    {
        return $settings->engagement->reviewRequestsEnabled;
    }

    /** @return Collection<int, EngagementRecipient> */
    public function findRecipients(TenantSettings $settings): Collection
    {
        $delayHours = $settings->engagement->reviewRequestDelayHours;

        return Order::query()
            ->delivered()
            ->whereNull('review_request_sent_at')
            ->where('updated_at', '<=', now()->subHours($delayHours))
            ->whereIn('customer_id', Customer::query()->subscribedToMarketing()->whereNotNull('email')->select('id'))
            ->with('customer')
            ->get()
            ->map(function (Order $order): EngagementRecipient {
                /** @var Customer $customer */
                $customer = $order->customer;

                return new EngagementRecipient(
                    email: $customer->email,
                    name: $customer->name,
                    model: $order,
                );
            });
    }

    public function dispatchForRecipient(EngagementRecipient $recipient, TenantSettings $settings): void
    {
        /** @var Order $order */
        $order = $recipient->model;

        event(new ReviewRequested($order));

        $order->update(['review_request_sent_at' => now()]);
    }
}
