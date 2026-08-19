<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Queries\Orders\OrderFunnelQuery;
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
        return $this->cached('main', [60, 120], fn (): array => resolve(OrderFunnelQuery::class)->get());
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
