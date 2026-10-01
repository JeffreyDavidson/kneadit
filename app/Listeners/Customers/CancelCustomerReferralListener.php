<?php

declare(strict_types=1);

namespace App\Listeners\Customers;

use App\Enums\Customers\CustomerReferralStatus;
use App\Events\Orders\OrderCancelled;
use App\Listeners\QueuedListener;
use App\Models\Customers\CustomerReferral;
use Illuminate\Support\Facades\Log;

/**
 * A pending referral earns no reward when the referred order is cancelled.
 */
class CancelCustomerReferralListener extends QueuedListener
{
    public function handle(OrderCancelled $event): void
    {
        CustomerReferral::query()
            ->pending()
            ->forOrder($event->order)
            ->update(['status' => CustomerReferralStatus::Cancelled]);
    }

    public function failed(OrderCancelled $event, \Throwable $exception): void
    {
        Log::warning('Cancel customer referral failed', [
            'order' => $event->order->order_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
