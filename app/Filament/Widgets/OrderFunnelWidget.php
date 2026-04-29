<?php

namespace App\Filament\Widgets;

use App\Enums\Orders\OrderStatus;
use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Orders\Order;
use App\ValueObjects\Money;
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
            // SUM(total) bypasses MoneyCentsCast and returns cents directly.
            // See migrations under database/migrations/tenant/*_convert_*_money_columns_to_cents.php.
            $aggregates = Order::query()
                ->selectRaw('status, COUNT(*) as order_count, COALESCE(SUM(total), 0) as total_cents')
                ->groupBy('status')
                ->get()
                ->keyBy('status');

            return array_map(
                function (OrderStatus $status) use ($aggregates): array {
                    /** @var object{order_count: int|string, total_cents: int|string}|null $row */
                    $row = $aggregates[$status->value] ?? null;

                    return [
                        ...$status->toFunnelStage((int) ($row->order_count ?? 0)),
                        'total_formatted' => Money::fromCents((int) ($row->total_cents ?? 0))->formatted(),
                    ];
                },
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
