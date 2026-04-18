<?php

namespace App\Filament\Widgets;

use App\Enums\Orders\OrderStatus;
use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Models\Orders\Order;
use Filament\Widgets\Widget;

class OrderFunnelWidget extends Widget
{
    use CachesWidgetData;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.order-funnel';

    /** @return array<int, array<string, mixed>> */
    public function getStages(): array
    {
        return $this->cached('main', [60, 120], function (): array {
            $counts = Order::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            return array_map(
                fn (OrderStatus $status): array => $status->toFunnelStage((int) ($counts[$status->value] ?? 0)),
                OrderStatus::trackableStatuses(),
            );
        });
    }

    protected function cachePrefix(): string
    {
        return 'order_funnel';
    }
}
