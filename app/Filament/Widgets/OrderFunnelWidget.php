<?php

namespace App\Filament\Widgets;

use App\Enums\Orders\OrderStatus;
use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Orders\Order;
use Filament\Widgets\Widget;

class OrderFunnelWidget extends Widget
{
    use CachesWidgetData;
    use HasDashboardSize;

    protected static ?int $sort = 3;

    protected string $view = 'filament.widgets.order-funnel';

    /** @return array<int, array<string, mixed>> */
    public function getStages(): array
    {
        return $this->cached('main', [60, 120], function (): array {
            $counts = Order::query()
                ->selectRaw('status, COUNT(*) as order_count')
                ->groupBy('status')
                ->pluck('order_count', 'status');

            return array_map(
                fn (OrderStatus $status): array => $status->toFunnelStage((int) ($counts[$status->value] ?? 0)),
                OrderStatus::trackableStatuses(),
            );
        });
    }

    /**
     * sm shows only the actionable upstream stages (where the bakery
     * still has work to do); md shows the full funnel.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getVisibleStages(): array
    {
        $stages = $this->getStages();

        return $this->isSize('sm') ? array_slice($stages, 0, 3) : $stages;
    }

    protected function cachePrefix(): string
    {
        return 'order_funnel';
    }
}
