<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Models\Engagement\PageView;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StorefrontViewsWidget extends BaseWidget
{
    use CachesWidgetData;

    protected ?string $pollingInterval = null;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected function getStats(): array
    {
        $data = $this->cached('main', [60, 120], function (): array {
            $today = today();
            $chart = [];

            // 7-day series ending today (oldest → newest).
            for ($i = 6; $i >= 0; $i--) {
                $day = $today->copy()->subDays($i);
                $chart[] = PageView::query()->whereNull('product_id')
                    ->whereBetween('created_at', [$day->copy()->startOfDay(), $day->copy()->endOfDay()])
                    ->count();
            }

            $todayCount = $chart[6];
            $yesterdayCount = $chart[5];

            $trend = $yesterdayCount > 0
                ? (int) round((($todayCount - $yesterdayCount) / $yesterdayCount) * 100)
                : ($todayCount > 0 ? 100 : 0);

            return ['today' => $todayCount, 'trend' => $trend, 'chart' => $chart];
        });

        $trend = $data['trend'];
        $description = $trend >= 0 ? "{$trend}% above yesterday" : abs($trend) . '% below yesterday';
        $icon = $trend >= 0 ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown;
        $color = $trend >= 0 ? 'success' : 'warning';

        return [
            Stat::make('Storefront Views Today', Number::format($data['today']))
                ->description($description)
                ->descriptionIcon($icon)
                ->color($color)
                ->chart($data['chart'])
                ->chartColor($color)
                ->url('/admin/storefront-analytics'),
        ];
    }

    protected function cachePrefix(): string
    {
        return 'storefront_views';
    }
}
