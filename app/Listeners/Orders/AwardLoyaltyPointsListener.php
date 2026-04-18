<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderDelivered;
use App\Listeners\QueuedListener;
use App\Services\Loyalty\LoyaltyLedger;
use Illuminate\Support\Facades\Log;

class AwardLoyaltyPointsListener extends QueuedListener
{
    public function __construct(
        private LoyaltyLedger $loyaltyLedger,
    ) {}

    public function handle(OrderDelivered $event): void
    {
        $this->loyaltyLedger->creditOrder($event->order);
    }

    public function failed(OrderDelivered $event, \Throwable $exception): void
    {
        Log::warning('Award loyalty points failed', [
            'order' => $event->order->order_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
