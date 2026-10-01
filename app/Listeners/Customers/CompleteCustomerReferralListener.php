<?php

declare(strict_types=1);

namespace App\Listeners\Customers;

use App\Enums\Customers\CustomerReferralStatus;
use App\Events\Customers\CustomerReferralCompleted;
use App\Events\Orders\OrderDelivered;
use App\Listeners\QueuedListener;
use App\Models\Customers\CustomerReferral;
use Illuminate\Support\Facades\Log;

/**
 * A referral is earned once the referred customer's order is delivered.
 */
class CompleteCustomerReferralListener extends QueuedListener
{
    public function handle(OrderDelivered $event): void
    {
        $referral = CustomerReferral::query()->pending()->forOrder($event->order)->first();

        if (! $referral instanceof CustomerReferral) {
            return;
        }

        $referral->update([
            'status' => CustomerReferralStatus::Completed,
            'completed_at' => now(),
        ]);

        event(new CustomerReferralCompleted($referral));
    }

    public function failed(OrderDelivered $event, \Throwable $exception): void
    {
        Log::warning('Complete customer referral failed', [
            'order' => $event->order->order_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
