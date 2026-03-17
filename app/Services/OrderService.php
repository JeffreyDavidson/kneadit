<?php

namespace App\Services;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Models\CapacityLimit;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\GiftCard;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected CouponService $couponService,
        protected GiftCardService $giftCardService,
    ) {}

    /**
     * Load products, calculate line items, subtotal, delivery fee, and total.
     *
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     * @return array{items: array, subtotal: float, delivery_fee: float, total: float}
     */
    public function calculateOrder(array $items, string $deliveryType, ?string $deliveryTier = null): array
    {
        $subtotal = 0;
        $orderItems = [];

        $productIds = array_column($items, 'product_id');
        $products = Product::findOrFail($productIds)->keyBy('id');

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

    /**
     * Create an order inside a DB transaction with capacity checks,
     * coupon/gift card redemption, and order item creation.
     *
     * Returns null if the delivery date is fully booked.
     */
    public function createOrder(array $data, ?int $couponId = null, ?int $giftCardId = null): ?Order
    {
        $calculated = $this->calculateOrder(
            $data['items'],
            $data['delivery_type'],
            $data['delivery_tier'] ?? null,
        );

        if (empty($calculated['items'])) {
            return null;
        }

        return DB::transaction(function () use ($data, $calculated, $couponId, $giftCardId) {
            // Check capacity limits inside transaction for atomicity
            if (! CapacityLimit::isAvailable($data['delivery_date'])) {
                return null;
            }

            // Apply coupon via service (uses lockForUpdate for thread safety)
            if ($couponId) {
                $coupon = Coupon::lockForUpdate()->find($couponId);
                if ($coupon && $coupon->isValid()) {
                    $discountAmount = $coupon->calculateDiscount($calculated['subtotal']);
                    $calculated['discount_amount'] = $discountAmount;
                    $calculated['coupon_id'] = $coupon->id;
                    $calculated['total'] = max(0, $calculated['total'] - $discountAmount);
                }
            }

            // Create or update customer
            $customer = Customer::updateOrCreate(
                ['email' => $data['customer_email']],
                array_filter([
                    'name' => $data['customer_name'],
                    'phone' => $data['customer_phone'] ?? null,
                    'birthday' => $data['customer_birthday'] ?? null,
                ], fn ($v) => $v !== null)
            );

            // Create order
            $order = Order::create([
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

            // Increment coupon usage atomically (already locked above)
            if (! empty($calculated['coupon_id'])) {
                $this->couponService->apply($coupon);
            }

            // Redeem gift card if provided
            if ($giftCardId) {
                $giftCard = GiftCard::lockForUpdate()->find($giftCardId);
                if ($giftCard && $giftCard->isUsable()) {
                    $gcAmount = min((float) $giftCard->current_balance, (float) $order->total);
                    if ($gcAmount > 0) {
                        $this->giftCardService->redeem($giftCard->code, $gcAmount, $order->id);
                        $order->update(['total' => max(0, (float) $order->total - $gcAmount)]);
                    }
                }
            }

            // Create order items
            foreach ($calculated['items'] as $item) {
                $order->orderItems()->create($item);
            }

            return $order;
        });
    }

    /**
     * Generate a unique order number in the format KNyymmddXXXX.
     */
    public function generateOrderNumber(): string
    {
        do {
            $number = 'KN'.date('ymd').strtoupper(Str::random(4));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
