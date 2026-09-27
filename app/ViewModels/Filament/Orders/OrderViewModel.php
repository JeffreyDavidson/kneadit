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
            OrderStatus::Pending => ['bg' => 'bg-amber-500/15', 'border' => 'border-amber-500/25', 'text' => 'text-amber-400'],
            OrderStatus::Confirmed => ['bg' => 'bg-sky-500/15', 'border' => 'border-sky-500/25', 'text' => 'text-sky-400'],
            OrderStatus::Baking => ['bg' => 'bg-orange-500/15', 'border' => 'border-orange-500/25', 'text' => 'text-orange-400'],
            OrderStatus::Ready, OrderStatus::Delivered => ['bg' => 'bg-emerald-500/15', 'border' => 'border-emerald-500/25', 'text' => 'text-emerald-400'],
            OrderStatus::Cancelled => ['bg' => 'bg-red-500/15', 'border' => 'border-red-500/25', 'text' => 'text-red-400'],
        };

        $this->paymentColor = match ($order->payment_status) {
            PaymentStatus::Paid => ['bg' => 'bg-emerald-500/15', 'border' => 'border-emerald-500/25', 'text' => 'text-emerald-400'],
            PaymentStatus::Unpaid => ['bg' => 'bg-red-500/15', 'border' => 'border-red-500/25', 'text' => 'text-red-400'],
            PaymentStatus::Refunded => ['bg' => 'bg-amber-500/15', 'border' => 'border-amber-500/25', 'text' => 'text-amber-400'],
            default => ['bg' => 'bg-brand-800', 'border' => 'border-brand-700', 'text' => 'text-brand-200'],
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
