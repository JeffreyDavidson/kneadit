<?php

namespace App\ViewModels\Filament\Orders;

use App\Enums\Orders\DeliveryType;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Models\Orders\Order;
use App\Models\Orders\OrderMessage;
use Illuminate\Database\Eloquent\Collection;

final readonly class OrderViewModel
{
    /** @var array{bg: string, border: string, text: string} */
    public array $statusColor;

    /** @var array{bg: string, border: string, text: string} */
    public array $paymentColor;

    public bool $isDelivery;

    public bool $hasPickupContact;

    /**
     * @var array{
     *     overview: array{label: string, icon: string},
     *     items: array{label: string, icon: string, count: int},
     *     activity: array{label: string, icon: string, count: int}
     * }
     */
    public array $tabs;

    /** @var Collection<int, OrderMessage> */
    public Collection $messages;

    public function __construct(public Order $order)
    {
        $this->statusColor = match ($order->status) {
            OrderStatus::Pending => ['bg' => 'bg-(--kn-warning-tint)', 'border' => 'border-(--kn-warning)', 'text' => 'text-(--kn-warning)'],
            OrderStatus::Confirmed => ['bg' => 'bg-(--kn-info-tint)', 'border' => 'border-(--kn-info)', 'text' => 'text-(--kn-info)'],
            OrderStatus::Baking => ['bg' => 'bg-(--kn-warning-tint)', 'border' => 'border-(--kn-honey)', 'text' => 'text-(--kn-honey-text)'],
            OrderStatus::Ready, OrderStatus::Delivered => ['bg' => 'bg-(--kn-success-tint)', 'border' => 'border-(--kn-success)', 'text' => 'text-(--kn-success)'],
            OrderStatus::Cancelled => ['bg' => 'bg-(--kn-danger-tint)', 'border' => 'border-(--kn-danger)', 'text' => 'text-(--kn-danger)'],
        };

        $this->paymentColor = match ($order->payment_status) {
            PaymentStatus::Paid => ['bg' => 'bg-(--kn-success-tint)', 'border' => 'border-(--kn-success)', 'text' => 'text-(--kn-success)'],
            PaymentStatus::Unpaid => ['bg' => 'bg-(--kn-danger-tint)', 'border' => 'border-(--kn-danger)', 'text' => 'text-(--kn-danger)'],
            PaymentStatus::Refunded => ['bg' => 'bg-(--kn-warning-tint)', 'border' => 'border-(--kn-warning)', 'text' => 'text-(--kn-warning)'],
            default => ['bg' => 'bg-(--kn-surface-hover)', 'border' => 'border-(--kn-border)', 'text' => 'text-(--kn-ink-2)'],
        };

        $this->isDelivery = $order->delivery_type === DeliveryType::Delivery;
        $this->hasPickupContact = filled($order->pickup_contact_name);
        $this->tabs = [
            'overview' => ['label' => 'Overview', 'icon' => 'chart-bar-square'],
            'items' => ['label' => 'Items', 'icon' => 'shopping-bag', 'count' => $order->orderItems->count()],
            'activity' => ['label' => 'Activity', 'icon' => 'clock', 'count' => $order->messages->count()],
        ];
        $this->messages = $order->messages->sortBy('created_at');
    }
}
