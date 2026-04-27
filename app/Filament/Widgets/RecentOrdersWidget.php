<?php

namespace App\Filament\Widgets;

use App\Enums\Filament\WidgetSize;
use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Orders\Order;
use Filament\Widgets\Widget;

class RecentOrdersWidget extends Widget
{
    use CachesWidgetData;
    use HasDashboardSize;

    protected static ?int $sort = 4;

    protected string $view = 'filament.widgets.recent-orders';

    /** @return array<int, array<string, mixed>> */
    public function getRows(): array
    {
        $limit = $this->rowLimit();

        return $this->cached("main_{$limit}", [120, 300], fn (): array => Order::query()
            ->with('customer')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Order $order): array => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer' => $order->customer?->name ?? 'Walk-in',
                'total' => $order->total->formatted(),
                'status' => $order->status,
                'dot_color' => $order->status->funnelDotColor(),
                'when' => $order->created_at?->diffForHumans(),
            ])
            ->all());
    }

    public function getViewAllUrl(): string
    {
        return route('filament.admin.resources.orders.index');
    }

    private function rowLimit(): int
    {
        return match ($this->size()) {
            WidgetSize::Small => 3,
            WidgetSize::Medium => 5,
            default => 10,
        };
    }

    protected function cachePrefix(): string
    {
        return 'recent_orders';
    }
}
