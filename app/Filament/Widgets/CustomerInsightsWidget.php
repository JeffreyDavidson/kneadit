<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Customers\Customer;
use App\Queries\Analytics\CustomerInsightsQuery;
use App\Services\Scheduling\BakeryClock;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Config;

class CustomerInsightsWidget extends Widget
{
    use CachesWidgetData;
    use HasDashboardSize;

    #[\Override]
    protected static ?int $sort = 8;

    #[\Override]
    protected string $view = 'filament.widgets.customer-insights';

    public function getNewCustomersThisWeek(): int
    {
        $weekStart = resolve(BakeryClock::class)->now()->startOfWeek();
        $weekKey = $weekStart->format('Y-W');

        return $this->cached("new_{$weekKey}", [900, 1800], fn (): int => Customer::query()
            ->where('created_at', '>=', $weekStart->copy()->setTimezone(Config::string('app.timezone')))
            ->count());
    }

    public function getRepeatCustomerRate(): float
    {
        return $this->cached('repeat', [3600, 7200], function (): float {
            $counts = resolve(CustomerInsightsQuery::class)->repeatCustomerCounts();
            if ($counts['total_with_orders'] === 0) {
                return 0;
            }

            return round(($counts['repeat_customers'] / $counts['total_with_orders']) * 100, 1);
        });
    }

    /** @return array<string, mixed> */
    public function getAvgOrderValue(): array
    {
        $monthKey = resolve(BakeryClock::class)->now()->format('Y-m');

        return $this->cached("aov_{$monthKey}", [900, 1800], function (): array {
            $averages = resolve(CustomerInsightsQuery::class)->averageOrderValues();

            return [
                'value' => $averages['this_month'],
                'trend' => $averages['this_month']->cents() >= $averages['last_month']->cents() ? 'up' : 'down',
            ];
        });
    }

    protected function cachePrefix(): string
    {
        return 'customer_insights';
    }
}
