<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderTransitionException;
use App\Mail\OrderBaking;
use App\Mail\OrderCancelled;
use App\Mail\OrderConfirmed;
use App\Mail\OrderDelivered;
use App\Mail\OrderReady;
use App\Models\Order;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TransitionOrderStatus
{
    /** @var array<string, array<string>> */
    private const TRANSITIONS = [
        OrderStatus::Pending->value => [OrderStatus::Confirmed->value, OrderStatus::Cancelled->value],
        OrderStatus::Confirmed->value => [OrderStatus::Baking->value, OrderStatus::Cancelled->value],
        OrderStatus::Baking->value => [OrderStatus::Ready->value, OrderStatus::Cancelled->value],
        OrderStatus::Ready->value => [OrderStatus::Delivered->value],
    ];

    public function __construct(
        protected AwardLoyaltyPoints $awardLoyaltyPoints,
        protected DeductIngredients $deductIngredients,
    ) {}

    public function __invoke(Order $order, OrderStatus $to): Order
    {
        $from = $order->status;
        $allowed = self::TRANSITIONS[$from->value] ?? [];

        if (! in_array($to->value, $allowed)) {
            throw new InvalidOrderTransitionException($order, $from, $to);
        }

        $order->update(['status' => $to]);

        try {
            $this->sendStatusEmail($order);
        } catch (\Exception $e) {
            Log::warning('Order status email failed', [
                'order' => $order->order_number,
                'status' => $order->status,
                'error' => $e->getMessage(),
            ]);
        }

        if ($to === OrderStatus::Baking) {
            try {
                ($this->deductIngredients)($order);
            } catch (\Exception $e) {
                Log::warning('Ingredient deduction failed', [
                    'order' => $order->order_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($to === OrderStatus::Delivered) {
            try {
                ($this->awardLoyaltyPoints)($order);
            } catch (\Exception $e) {
                Log::warning('Loyalty points award failed', [
                    'order' => $order->order_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        try {
            WebhookService::dispatch('order.updated', [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'previous_status' => $from,
                'payment_status' => $order->payment_status,
                'total' => $order->total,
            ]);
        } catch (\Exception $e) {
            Log::warning('Order updated webhook dispatch failed', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }

        return $order;
    }

    /**
     * @return array<OrderStatus>
     */
    public static function allowedTransitions(Order $order): array
    {
        $allowed = self::TRANSITIONS[$order->status->value] ?? [];

        return array_map(fn (string $s) => OrderStatus::from($s), $allowed);
    }

    private function sendStatusEmail(Order $order): void
    {
        if (! $order->customer?->email) {
            return;
        }

        $email = $order->customer->email;

        match ($order->status) {
            OrderStatus::Confirmed => Mail::to($email)->send(new OrderConfirmed($order)),
            OrderStatus::Baking => Mail::to($email)->send(new OrderBaking($order)),
            OrderStatus::Ready => Mail::to($email)->send(new OrderReady($order)),
            OrderStatus::Delivered => Mail::to($email)->send(new OrderDelivered($order)),
            OrderStatus::Cancelled => Mail::to($email)->send(new OrderCancelled($order)),
            default => null,
        };
    }
}
