<?php

namespace App\Filament\Widgets;

use App\Enums\Orders\OrderStatus;
use App\Models\Orders\Order;
use Filament\Widgets\Widget;

class OrderFunnelWidget extends Widget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.order-funnel';

    /** @return array<int, array<string, mixed>> */
    public function getStages(): array
    {
        $counts = Order::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stages = [
            ['key' => OrderStatus::Pending->value, 'label' => 'Pending', 'color' => '#F59E0B', 'bg' => '#FEF3C7'],
            ['key' => OrderStatus::Confirmed->value, 'label' => 'Confirmed', 'color' => '#3B82F6', 'bg' => '#DBEAFE'],
            ['key' => OrderStatus::Baking->value, 'label' => 'Baking', 'color' => '#8B5E3C', 'bg' => '#F5E6D3'],
            ['key' => OrderStatus::Ready->value, 'label' => 'Ready', 'color' => '#10B981', 'bg' => '#D1FAE5'],
            ['key' => OrderStatus::Delivered->value, 'label' => 'Delivered', 'color' => '#6B7280', 'bg' => '#F3F4F6'],
        ];

        foreach ($stages as &$stage) {
            $stage['count'] = (int) ($counts[$stage['key']] ?? 0);
        }

        return $stages;
    }
}
