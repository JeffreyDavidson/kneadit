<?php

namespace App\Queries\Analytics;

use App\Models\Engagement\PageView;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

final class StorefrontViewsQuery
{
    /** @return array{today: int, trend: int, chart: list<int>} */
    public function get(): array
    {
        $today = Date::today();
        $start = $today->copy()->subDays(6)->startOfDay();
        $end = $today->copy()->endOfDay();

        $viewsByDate = PageView::query()
            ->whereNull('product_id')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as view_date, COUNT(*) as view_count')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get()
            ->mapWithKeys(function (PageView $pageView): array {
                $attributes = $pageView->getAttributes();

                return [Arr::string($attributes, 'view_date') => Arr::integer($attributes, 'view_count')];
            });

        $chart = [];
        for ($daysAgo = 6; $daysAgo >= 0; $daysAgo--) {
            $chart[] = $viewsByDate->get($today->copy()->subDays($daysAgo)->toDateString(), 0);
        }

        $todayCount = $chart[6];
        $yesterdayCount = $chart[5];
        $trend = $yesterdayCount > 0
            ? (int) round((($todayCount - $yesterdayCount) / $yesterdayCount) * 100)
            : ($todayCount > 0 ? 100 : 0);
        $max = max($chart) ?: 1;

        return [
            'today' => $todayCount,
            'trend' => $trend,
            'chart' => array_map(fn (int $views): int => (int) round($views / $max * 100), $chart),
        ];
    }
}
