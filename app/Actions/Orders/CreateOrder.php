<?php

namespace App\Actions\Orders;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Events\OrderCreated;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\GiftCard;
use App\Models\Order;
use App\Models\Product;
use App\Services\CouponService;
use App\Services\GiftCardService;
use App\Services\Inventory\CapacityCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOrder
{
    public function __construct(
        protected CouponService $couponService,
        protected GiftCardService $giftCardService,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public function __invoke(array $data, ?int $couponId = null, ?int $giftCardId = null): ?Order
    {
        $calculated = $this->calculateOrder(
            $data['items'],
            $data['delivery_type'],
            $data['delivery_tier'] ?? null,
        );

        if (empty($calculated['items'])) {
            return null;
        }

        $order = DB::transaction(function () use ($data, $calculated, $couponId, $giftCardId) {
            if (! resolve(CapacityCalculator::class)->isAvailable($data['delivery_date'])) {
                return null;
            }

            if ($couponId) {
                $coupon = Coupon::query()->lockForUpdate()->find($couponId);
                if ($coupon && $coupon->isValid()) {
                    $discountAmount = $coupon->calculateDiscount($calculated['subtotal']);
                    $calculated['discount_amount'] = $discountAmount;
                    $calculated['coupon_id'] = $coupon->id;
                    $calculated['total'] = max(0, $calculated['total'] - $discountAmount);
                }
            }

            $customer = Customer::query()->updateOrCreate(['email' => $data['customer_email']], array_filter([
                'name' => $data['customer_name'],
                'phone' => $data['customer_phone'] ?? null,
                'birthday' => $data['customer_birthday'] ?? null,
            ], fn (mixed $v) => $v !== null));

            $order = Order::query()->create([
                'customer_id' => $customer->id,
                'order_number' => $this->generateOrderNumber(),
                'delivery_date' => $data['delivery_date'],
                'delivery_time' => $data['delivery_time'] ?? null,
                'delivery_type' => $data['delivery_type'],
                'delivery_address' => $data['delivery_address'] ?? null,
                'subtotal' => $calculated['subtotal'],
                'delivery_fee' => $calculated['delivery_fee'],
                'discount_amount' => $calculated['discount_amount'] ?? 0,
                'coupon_id' => $calculated['coupon_id'] ?? null,
                'total' => $calculated['total'],
                'notes' => $data['notes'] ?? null,
                'status' => OrderStatus::Pending,
            ]);

            if (! empty($calculated['coupon_id']) && isset($coupon)) {
                $this->couponService->apply($coupon);
            }

            if ($giftCardId) {
                $giftCard = GiftCard::query()->lockForUpdate()->find($giftCardId);
                if ($giftCard && $giftCard->is_usable) {
                    $gcAmount = min((float) $giftCard->current_balance, (float) $order->total);
                    if ($gcAmount > 0) {
                        $this->giftCardService->redeem($giftCard->code, $gcAmount, $order->id);
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
            $deliveryFee = match ($deliveryTier) {
                'under5' => 0,
                '5to10' => 5.00,
                '10to15' => 10.00,
                'over15' => 15.00,
                default => 0,
            };
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
            $number = 'KN' . date('ymd') . strtoupper(Str::random(4));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
