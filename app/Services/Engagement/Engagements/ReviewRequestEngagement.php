<?php

namespace App\Services\Engagement\Engagements;

use App\Builders\Orders\OrderQueryBuilder;
use App\Events\Customers\ReviewRequested;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Services\Engagement\Contracts\CustomerEngagement;
use App\Services\Engagement\Contracts\EngagementRecipient;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Collection;

class ReviewRequestEngagement implements CustomerEngagement
{
    /** Only orders delivered within this many days are asked for a review. */
    private const int WINDOW_DAYS = 7;

    public function isEnabled(TenantSettings $settings): bool
    {
        return $settings->engagement->reviewRequestsEnabled;
    }

    /**
     * One recipient per customer: their most recent eligible order. The other
     * eligible orders are marked handled when that one is sent, so they do not
     * trickle out on later runs.
     *
     * @return Collection<int, EngagementRecipient>
     */
    public function findRecipients(TenantSettings $settings): Collection
    {
        return $this->eligibleOrders($settings)
            ->with('customer')
            ->latest('updated_at')
            ->latest('id')
            ->get()
            ->unique('customer_id')
            ->map(function (Order $order): EngagementRecipient {
                /** @var Customer $customer */
                $customer = $order->customer;

                return new EngagementRecipient(
                    email: $customer->email,
                    name: $customer->name,
                    model: $order,
                );
            })
            ->values();
    }

    public function dispatchForRecipient(EngagementRecipient $recipient, TenantSettings $settings): void
    {
        /** @var Order $order */
        $order = $recipient->model;

        event(new ReviewRequested($order));

        $order->update(['review_request_sent_at' => now()]);

        $this->eligibleOrders($settings)
            ->where('customer_id', $order->customer_id)
            ->whereKeyNot($order->getKey())
            ->update(['review_request_sent_at' => now()]);
    }

    /**
     * Delivered orders past the delay and no older than the window, so turning
     * the feature on does not email the whole order history.
     */
    private function eligibleOrders(TenantSettings $settings): OrderQueryBuilder
    {
        return Order::query()
            ->delivered()
            ->whereNull('review_request_sent_at')
            ->where('updated_at', '<=', now()->subHours($settings->engagement->reviewRequestDelayHours))
            ->where('updated_at', '>=', now()->subDays(self::WINDOW_DAYS))
            ->whereIn('customer_id', Customer::query()->subscribedToMarketing()->whereNotNull('email')->select('id'));
    }
}
