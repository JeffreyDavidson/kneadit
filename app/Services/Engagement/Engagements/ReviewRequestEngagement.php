<?php

namespace App\Services\Engagement\Engagements;

use App\Contracts\Engagement\CustomerEngagement;
use App\Contracts\Engagement\EngagementRecipient;
use App\Enums\Orders\OrderStatus;
use App\Events\Customers\ReviewRequested;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\Database\Eloquent\Builder;
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
            ->where('status', OrderStatus::Delivered)
            ->whereNull('review_request_sent_at')
            ->where('updated_at', '<=', now()->subHours($delayHours))
            ->whereHas('customer', fn (Builder $q) => $q->whereNotNull('email'))
            ->with('customer')
            ->get()
            ->map(fn (Order $order) => new EngagementRecipient(
                email: $order->customer->email,
                name: $order->customer->name,
                model: $order,
            ));
    }

    public function dispatchForRecipient(EngagementRecipient $recipient, TenantSettings $settings): void
    {
        /** @var Order $order */
        $order = $recipient->model;

        ReviewRequested::dispatch($order);

        $order->update(['review_request_sent_at' => now()]);
    }
}
