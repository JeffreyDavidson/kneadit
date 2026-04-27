<?php

namespace App\Filament\Widgets;

use App\Enums\Filament\WidgetSize;
use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Queries\Financial\RevenueQuery;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Number;

class RevenueChartWidget extends ChartWidget
{
    use CachesWidgetData;
    use HasDashboardSize;

    protected static ?int $sort = 3;

    protected ?string $heading = 'Revenue — Last 30 Days';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $days = $this->windowDays();
        $cached = $this->cached("main_{$days}", [300, 600], fn (): array => $this->computeData($days));

        $this->heading = $cached['heading'];

        return $cached['chart'];
    }

    protected function cachePrefix(): string
    {
        return 'revenue_chart';
    }

    private function windowDays(): int
    {
        // revenue_chart is constrained to md/lg in WidgetMeta. md preserves
        // the original 30-day window; lg widens to 90 days for fuller
        // historical context. (sm is unreachable but defaults to 30 anyway.)
        return match ($this->size()) {
            WidgetSize::Large => 90,
            default => 30,
        };
    }

    /** @return array{heading: string, chart: array<string, mixed>} */
    private function computeData(int $windowDays): array
    {
        $end = Date::today();
        $start = $end->copy()->subDays($windowDays - 1);
        $prevEnd = $start->copy()->subDay();
        $prevStart = $prevEnd->copy()->subDays($windowDays - 1);

        $current = $this->getDailyRevenue($start, $end);
        $previous = $this->getDailyRevenue($prevStart, $prevEnd);

        /** @var Collection<int, Carbon> $period */
        $period = collect(iterator_to_array(CarbonPeriod::create($start, $end)));
        $labels = $period
            ->map(fn (Carbon $d) => $d->format('M j'))
            ->all();

        $currentTotal = array_sum($current);
        $previousTotal = array_sum($previous);
        $change = $previousTotal > 0
            ? round((($currentTotal - $previousTotal) / $previousTotal) * 100, 1)
            : ($currentTotal > 0 ? 100 : 0);

        $arrow = $change >= 0 ? '↑' : '↓';

        return [
            'heading' => "Revenue — Last {$windowDays} Days · \${$this->fmt($currentTotal)} ({$arrow} {$change}%)",
            'chart' => [
                'datasets' => [
                    [
                        'label' => 'This Period',
                        'data' => $current,
                        'borderColor' => '#8B5E3C',
                        'backgroundColor' => 'rgba(139, 94, 60, 0.1)',
                        'fill' => true,
                        'tension' => 0.3,
                    ],
                    [
                        'label' => 'Previous Period',
                        'data' => $previous,
                        'borderColor' => '#D4A574',
                        'backgroundColor' => 'transparent',
                        'borderDash' => [5, 5],
                        'tension' => 0.3,
                    ],
                ],
                'labels' => $labels,
            ],
        ];
    }

    /** @return array<int, float> */
    private function getDailyRevenue(Carbon $start, Carbon $end): array
    {
        $raw = RevenueQuery::dailyBreakdown([$start, $end]);

        /** @var Collection<int, Carbon> $days */
        $days = collect(iterator_to_array(CarbonPeriod::create($start, $end)));

        return $days
            ->map(fn (Carbon $d) => (float) ($raw[$d->format('Y-m-d')] ?? 0))
            ->all();
    }

    private function fmt(float $v): string
    {
        return (string) Number::format($v);
    }
}
