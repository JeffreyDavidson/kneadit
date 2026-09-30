<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Models\Engagement\PageView;
use App\Services\Scheduling\BakeryClock;
use Filament\Widgets\Widget;

class StorefrontViewsWidget extends Widget
{
    use CachesWidgetData;

    #[\Override]
    protected static ?int $sort = 2;

    #[\Override]
    protected string $view = 'filament.widgets.storefront-views';

    /** @return array<string, mixed> */
    public function getCardData(): array
    {
        return $this->cached('main', [60, 120], function (): array {
            $now = resolve(BakeryClock::class)->now();
            $appTimezone = config()->string('app.timezone');
            $chart = [];

            for ($i = 6; $i >= 0; $i--) {
                // Bakery-local day boundaries, converted to the app timezone that created_at is stored in.
                $day = $now->copy()->subDays($i);
                $chart[] = PageView::query()->whereNull('product_id')
                    ->whereBetween('created_at', [
                        $day->copy()->startOfDay()->setTimezone($appTimezone),
                        $day->copy()->endOfDay()->setTimezone($appTimezone),
                    ])
                    ->count();
            }

            $todayCount = $chart[6];
            $yesterdayCount = $chart[5];

            $trend = $yesterdayCount > 0
                ? (int) round((($todayCount - $yesterdayCount) / $yesterdayCount) * 100)
                : ($todayCount > 0 ? 100 : 0);

            $max = max($chart) ?: 1;
            $normalised = array_map(fn (int $v): int => (int) round($v / $max * 100), $chart);

            return [
                'today' => $todayCount,
                'trend' => $trend,
                'chart' => $normalised,
            ];
        });
    }

    public function getViewAllUrl(): string
    {
        return '/admin/storefront-analytics';
    }

    protected function cachePrefix(): string
    {
        return 'storefront_views';
    }
}
