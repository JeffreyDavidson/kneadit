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
            $today = PageView::query()->whereNull('product_id')
                ->where('created_at', '>=', today())
                ->count();

            $yesterday = PageView::query()->whereNull('product_id')
                ->whereBetween('created_at', [now()->subDay()->startOfDay(), today()])
                ->count();

            $trend = $yesterday > 0
                ? round((($today - $yesterday) / $yesterday) * 100)
                : ($today > 0 ? 100 : 0);

            return ['today' => $today, 'trend' => $trend];
        });

        $trend = $data['trend'];
        $description = $trend >= 0 ? "{$trend}% increase vs yesterday" : abs($trend) . '% decrease vs yesterday';
        $icon = $trend >= 0 ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown;
        $color = $trend >= 0 ? 'success' : 'danger';

        return [
            Stat::make('Storefront Views Today', Number::format($data['today']))
                ->description($description)
                ->descriptionIcon($icon)
                ->color($color)
                ->url('/admin/storefront-analytics'),
        ];
    }

    protected function cachePrefix(): string
    {
        return 'storefront_views';
    }
}
