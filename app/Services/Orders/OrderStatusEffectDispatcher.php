<?php

namespace App\Services\Orders;

use App\Enums\Orders\OrderStatus;
use App\Mail\Orders\OrderBakingMail;
use App\Mail\Orders\OrderCancelledMail;
use App\Mail\Orders\OrderConfirmedMail;
use App\Mail\Orders\OrderDeliveredMail;
use App\Mail\Orders\OrderReadyMail;
use App\Models\Orders\Order;
use App\Services\Inventory\InventoryManager;
use App\Services\Loyalty\LoyaltyLedger;
use App\Services\Platform\WebhookService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderStatusEffectDispatcher
{
    public function __construct(
        private LoyaltyLedger $loyaltyLedger,
        private InventoryManager $inventoryManager,
    ) {}

    public function dispatch(Order $order, OrderStatus $from, OrderStatus $to): void
    {
        $effects = $this->effectMap()[$to->value] ?? [];

        foreach ($effects as $method) {
            try {
                $this->{$method}($order, $from, $to);
            } catch (\Throwable $e) {
                Log::warning("Order effect [{$method}] failed", [
                    'order' => $order->order_number,
                    'transition' => "{$from->value} -> {$to->value}",
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Declarative map: status => effects to run.
     * Scan this to know exactly what every transition does.
     *
     * @return array<string, array<int, string>>
     */
    private function effectMap(): array
    {
        return [
            OrderStatus::Confirmed->value => ['sendEmail', 'dispatchWebhook'],
            OrderStatus::Baking->value => ['sendEmail', 'deductIngredients', 'dispatchWebhook'],
            OrderStatus::Ready->value => ['sendEmail', 'dispatchWebhook'],
            OrderStatus::Delivered->value => ['sendEmail', 'awardLoyaltyPoints', 'dispatchWebhook'],
            OrderStatus::Cancelled->value => ['sendEmail', 'dispatchWebhook'],
        ];
    }

    private function sendEmail(Order $order, OrderStatus $from, OrderStatus $to): void
    {
        if (! $order->customer?->email) {
            return;
        }

        $order->loadMissing('orderItems.product');

        $mailable = match ($to) {
            OrderStatus::Confirmed => new OrderConfirmedMail($order),
            OrderStatus::Baking => new OrderBakingMail($order),
            OrderStatus::Ready => new OrderReadyMail($order),
            OrderStatus::Delivered => new OrderDeliveredMail($order),
            OrderStatus::Cancelled => new OrderCancelledMail($order),
            default => null,
        };

        if ($mailable) {
            Mail::to($order->customer->email)->queue($mailable);
        }
    }

    private function deductIngredients(Order $order, OrderStatus $from, OrderStatus $to): void
    {
        $this->inventoryManager->deductForOrder($order);
    }

    private function awardLoyaltyPoints(Order $order, OrderStatus $from, OrderStatus $to): void
    {
        $this->loyaltyLedger->creditOrder($order);
    }

    private function dispatchWebhook(Order $order, OrderStatus $from, OrderStatus $to): void
    {
        WebhookService::dispatch('order.updated', [
            'order_number' => $order->order_number,
            'status' => $to->value,
            'previous_status' => $from->value,
            'payment_status' => $order->payment_status,
            'total' => $order->total,
        ]);
    }
}
