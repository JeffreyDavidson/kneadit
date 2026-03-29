<?php

namespace App\Actions\Orders;

use App\Actions\GiftCards\RedeemGiftCard;
use App\DataTransferObjects\CreateOrderData;
use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Events\OrderCreated;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\GiftCard;
use App\Models\Order;
use App\Models\Product;
use App\Services\CouponService;
use App\Services\Inventory\CapacityCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOrder
{
    public function __construct(
        protected CouponService $couponService,
        protected RedeemGiftCard $redeemGiftCard,
        protected CapacityCalculator $capacityCalculator,
    ) {}

    public function __invoke(CreateOrderData $data): ?Order
    {
        $calculated = $this->calculateOrder(
            $data->items,
            $data->deliveryType,
            $data->deliveryTier,
        );

        if (empty($calculated['items'])) {
            return null;
        }

        $order = DB::transaction(function () use ($data, $calculated) {
            if (! $this->capacityCalculator->isAvailable($data->deliveryDate)) {
                return null;
            }

            if ($data->couponId) {
                $coupon = Coupon::query()->lockForUpdate()->find($data->couponId);
                if ($coupon && $coupon->isValid()) {
                    $discountAmount = $coupon->calculateDiscount($calculated['subtotal']);
                    $calculated['discount_amount'] = $discountAmount;
                    $calculated['coupon_id'] = $coupon->id;
                    $calculated['total'] = max(0, $calculated['total'] - $discountAmount);
                }
            }

            $customer = Customer::query()->updateOrCreate(['email' => $data->customerEmail], array_filter([
                'name' => $data->customerName,
                'phone' => $data->customerPhone,
                'birthday' => $data->customerBirthday,
            ], fn (mixed $v) => $v !== null));

            $order = Order::query()->create([
                'customer_id' => $customer->id,
                'order_number' => $this->generateOrderNumber(),
                'delivery_date' => $data->deliveryDate,
                'delivery_time' => $data->deliveryTime,
                'delivery_type' => $data->deliveryType,
                'delivery_address' => $data->deliveryAddress,
                'subtotal' => $calculated['subtotal'],
                'delivery_fee' => $calculated['delivery_fee'],
                'discount_amount' => $calculated['discount_amount'] ?? 0,
                'coupon_id' => $calculated['coupon_id'] ?? null,
                'total' => $calculated['total'],
                'notes' => $data->notes,
                'status' => OrderStatus::Pending,
            ]);

            if (! empty($calculated['coupon_id']) && isset($coupon)) {
                $this->couponService->apply($coupon);
            }

            if ($data->giftCardId) {
                $giftCard = GiftCard::query()->lockForUpdate()->find($data->giftCardId);
                if ($giftCard && $giftCard->is_usable) {
                    $gcAmount = min((float) $giftCard->current_balance, (float) $order->total);
                    if ($gcAmount > 0) {
                        ($this->redeemGiftCard)($giftCard->code, $gcAmount, $order->id);
                        $order->update(['total' => max(0, (float) $order->total - $gcAmount)]);
                    }
                }
            }

            foreach ($calculated['items'] as $item) {
                $order->orderItems()->create($item);
            }

            return $order;
        });

        if ($order) {
            event(new OrderCreated($order));
        }

        return $order;
    }

    /**
     * @param array<int, array{product_id: int, quantity: int}> $items
     * @return array{items: array<int, mixed>, subtotal: float, delivery_fee: float, total: float}
     */
    private function calculateOrder(array $items, string $deliveryType, ?string $deliveryTier = null): array
    {
        $subtotal = 0;
        $orderItems = [];

        $productIds = array_column($items, 'product_id');
        $products = Product::query()->findOrFail($productIds)->keyBy('id');

        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            if (! $product || ! $product->is_active) {
                continue;
            }

            $lineTotal = $product->price * $item['quantity'];
            $subtotal += $lineTotal;

            $orderItems[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'total_price' => $lineTotal,
            ];
        }

        $deliveryFee = 0;
        if ($deliveryType === DeliveryType::Delivery->value) {
            $fees = config('kneadit.delivery_fees', []);
            $deliveryFee = $fees[$deliveryTier] ?? 0;
        }

        return [
            'items' => $orderItems,
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $subtotal + $deliveryFee,
        ];
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'KN' . date('ymd') . Str::upper(Str::random(4));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
