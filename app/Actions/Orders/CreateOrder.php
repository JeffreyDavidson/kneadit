<?php

namespace App\Actions\Orders;

use App\DataTransferObjects\CreateOrderData;
use App\Events\OrderCreated;
use App\Models\Order;
use App\Pipes\Orders\ApplyCoupon;
use App\Pipes\Orders\ApplyCouponUsage;
use App\Pipes\Orders\ApplyGiftCard;
use App\Pipes\Orders\CalculateOrderTotals;
use App\Pipes\Orders\OrderPipelineData;
use App\Pipes\Orders\PersistOrder;
use App\Pipes\Orders\PersistOrderItems;
use App\Pipes\Orders\ResolveCustomer;
use App\Pipes\Orders\ValidateCapacity;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;

class CreateOrder
{
    public function __invoke(CreateOrderData $data): ?Order
    {
        $payload = new OrderPipelineData($data);

        $result = DB::transaction(fn () => app(Pipeline::class)
            ->send($payload)
            ->through([
                CalculateOrderTotals::class,
                ValidateCapacity::class,
                ApplyCoupon::class,
                ResolveCustomer::class,
                PersistOrder::class,
                ApplyCouponUsage::class,
                ApplyGiftCard::class,
                PersistOrderItems::class,
            ])
            ->thenReturn());

        if ($result->cancelled || ! $result->order) {
            return null;
        }

        event(new OrderCreated($result->order));

        return $result->order;
    }
}
