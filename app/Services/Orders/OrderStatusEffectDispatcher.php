<?php

namespace App\Services\Orders;

use App\Actions\Orders\ReverseOrderDiscounts;
use App\Enums\Orders\OrderStatus;
use App\Mail\Orders\OrderStatusMail;
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
        private ReverseOrderDiscounts $reverseOrderDiscounts,
        private WebhookService $webhookService,
    ) {}

    public function dispatch(Order $order, OrderStatus $from, OrderStatus $to): void
    {
        $effects = $this->effectMap()[$to->value] ?? [];

        foreach ($effects as $effect) {
            $method = $effect['method'];
            $critical = $effect['critical'] ?? false;

            try {
                $this->{$method}($order, $from, $to);
            } catch (\Throwable $e) {
                if ($critical) {
                    throw $e;
                }

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
     *
     * Each effect has a method name and an optional critical flag.
     * Critical effects rethrow on failure, rolling back the status transition.
     * Non-critical effects log a warning and continue.
     *
     * @return array<string, array<int, array{method: string, critical?: bool}>>
     */
    private function effectMap(): array
    {
        return [
            OrderStatus::Confirmed->value => [
                ['method' => 'sendEmail'],
                ['method' => 'dispatchWebhook'],
            ],
            OrderStatus::Baking->value => [
                ['method' => 'sendEmail'],
                ['method' => 'deductIngredients', 'critical' => true],
                ['method' => 'dispatchWebhook'],
            ],
            OrderStatus::Ready->value => [
                ['method' => 'sendEmail'],
                ['method' => 'dispatchWebhook'],
            ],
            OrderStatus::Delivered->value => [
                ['method' => 'sendEmail'],
                ['method' => 'awardLoyaltyPoints'],
                ['method' => 'dispatchWebhook'],
            ],
            OrderStatus::Cancelled->value => [
                ['method' => 'sendEmail'],
                ['method' => 'reverseDiscounts', 'critical' => true],
                ['method' => 'dispatchWebhook'],
            ],
        ];
    }

    private function sendEmail(Order $order, OrderStatus $from, OrderStatus $to): void
    {
        if (! $order->customer?->email) {
            return;
        }

        $order->loadMissing('orderItems.product');

        Mail::to($order->customer->email)->queue(new OrderStatusMail($order, $to));
    }

    private function deductIngredients(Order $order, OrderStatus $from, OrderStatus $to): void
    {
        $this->inventoryManager->deductForOrder($order);
    }

    private function awardLoyaltyPoints(Order $order, OrderStatus $from, OrderStatus $to): void
    {
        $this->loyaltyLedger->creditOrder($order);
    }

    private function reverseDiscounts(Order $order, OrderStatus $from, OrderStatus $to): void
    {
        ($this->reverseOrderDiscounts)($order, "Order cancelled (was {$from->value})");
    }

    private function dispatchWebhook(Order $order, OrderStatus $from, OrderStatus $to): void
    {
        $this->webhookService->dispatch('order.updated', [
            'order_number' => $order->order_number,
            'status' => $to->value,
            'previous_status' => $from->value,
            'payment_status' => $order->payment_status,
            'total' => $order->total,
        ]);
    }
}
