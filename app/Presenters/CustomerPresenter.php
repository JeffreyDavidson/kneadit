<?php

namespace App\Presenters;

use App\DataTransferObjects\Customers\CustomerMetrics;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerNote;
use App\Models\Orders\Order;
use App\Services\Customers\CustomerIntelligence;
use Illuminate\Support\Carbon;

final class CustomerPresenter
{
    private ?CustomerMetrics $memo = null;

    private CustomerIntelligence $intelligence;

    public function __construct(
        public readonly Customer $customer,
        ?CustomerIntelligence $intelligence = null,
    ) {
        $this->intelligence = $intelligence ?? resolve(CustomerIntelligence::class);
    }

    public static function for(Customer $customer): self
    {
        return new self($customer);
    }

    public function totalPoints(): int
    {
        return $this->metrics()->totalPoints;
    }

    public function lifetimePointsEarned(): int
    {
        return $this->metrics()->lifetimePointsEarned;
    }

    public function lifetimeValue(): float
    {
        return $this->metrics()->lifetimeValue;
    }

    public function orderCount(): int
    {
        return $this->metrics()->orderCount;
    }

    public function lastOrderDate(): ?Carbon
    {
        return $this->metrics()->lastOrderDate;
    }

    public function averageOrderValue(): float
    {
        return $this->metrics()->averageOrderValue;
    }

    public function daysSinceLastOrder(): ?int
    {
        return $this->metrics()->daysSinceLastOrder;
    }

    public function isAtRisk(): bool
    {
        return $this->metrics()->isAtRisk;
    }

    public function __get(string $key): mixed
    {
        return $this->customer->{$key};
    }

    private function metrics(): CustomerMetrics
    {
        return $this->memo ??= $this->intelligence->metrics($this->customer);
    }

    /** @return array<string, mixed> */
    public function toDetailArray(): array
    {
        return [
            'id' => $this->customer->id,
            'name' => $this->customer->name,
            'email' => $this->customer->email,
            'phone' => $this->customer->phone,
            'address' => $this->customer->full_address,
            'orders' => $this->formattedOrders(),
            'notes' => $this->formattedNotes(),
            'stats' => $this->stats(),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    protected function formattedOrders(): array
    {
        return $this->customer->orders->map(fn (Order $order) => [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status->getLabel(),
            'payment_status' => $order->payment_status->getLabel(),
            'total' => $order->total->formatted(),
            'date' => $order->created_at?->format('M j, Y'),
            'delivery_date' => $order->delivery_date?->format('M j, Y'),
        ])->all();
    }

    /** @return array<int, array<string, mixed>> */
    protected function formattedNotes(): array
    {
        return $this->customer->customerNotes->map(fn (CustomerNote $note) => [
            'id' => $note->id,
            'note' => $note->note,
            'created_by' => $note->createdBy->name ?? 'Unknown',
            'created_at' => $note->created_at?->format('M j, Y g:i A'),
        ])->all();
    }

    /** @return array<string, mixed> */
    protected function stats(): array
    {
        $orders = $this->customer->orders;
        $totalSpent = $orders->sum('total');
        $orderCount = $orders->count();

        return [
            'total_orders' => $orderCount,
            'total_spent' => $totalSpent,
            'avg_order_value' => $orderCount > 0
                ? $totalSpent / $orderCount
                : 0,
            'last_order' => $orders->first()?->created_at?->format('M j, Y'),
        ];
    }
}
