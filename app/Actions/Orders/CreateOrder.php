<?php

namespace App\Actions\Orders;

use App\DataTransferObjects\Orders\CreateOrderData;
use App\Events\Orders\OrderCreated;
use App\Models\Orders\Order;
use App\Pipes\Orders\ApplyCoupon;
use App\Pipes\Orders\ApplyGiftCard;
use App\Pipes\Orders\ApplyReferral;
use App\Pipes\Orders\ApplySitewideSale;
use App\Pipes\Orders\ApplyTierPerks;
use App\Pipes\Orders\CalculateOrderTotals;
use App\Pipes\Orders\EnforceMinimumOrderAmount;
use App\Pipes\Orders\MarkCartConverted;
use App\Pipes\Orders\OrderPipelineData;
use App\Pipes\Orders\PersistOrder;
use App\Pipes\Orders\PersistOrderItems;
use App\Pipes\Orders\PersistReferralCompletion;
use App\Pipes\Orders\RecordCouponUsage;
use App\Pipes\Orders\RecordGiftCardRedemption;
use App\Pipes\Orders\ResolveCustomer;
use App\Pipes\Orders\ValidateCapacity;
use App\Pipes\Orders\ValidateStockAvailability;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;

class CreateOrder
{
    public function __invoke(CreateOrderData $data): ?Order
    {
        $payload = new OrderPipelineData($data);

        $result = DB::transaction(fn () => resolve(Pipeline::class)
            ->send($payload)
            ->through([
                CalculateOrderTotals::class,
                EnforceMinimumOrderAmount::class,
                ValidateCapacity::class,
                ValidateStockAvailability::class,
                ApplySitewideSale::class,
                ApplyCoupon::class,
                ApplyGiftCard::class,
                ApplyReferral::class,
                ResolveCustomer::class,
                ApplyTierPerks::class,
                PersistOrder::class,
                RecordCouponUsage::class,
                RecordGiftCardRedemption::class,
                PersistOrderItems::class,
                PersistReferralCompletion::class,
                MarkCartConverted::class,
            ])
            ->thenReturn());

        if ($result->cancelled || ! $result->order) {
            return null;
        }

        event(new OrderCreated($result->order));

        return $result->order;
    }
}
