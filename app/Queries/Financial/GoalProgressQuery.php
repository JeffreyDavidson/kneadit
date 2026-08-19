<?php

namespace App\Queries\Financial;

use App\Models\Orders\Order;
use App\Services\Settings\SettingsManager;
use App\ValueObjects\DateRange;
use Illuminate\Support\Facades\Date;

final class GoalProgressQuery
{
    public function __construct(private SettingsManager $settings) {}

    /** @return array{label: string, goal: float, revenue: float, percentage: float|int} */
    public function monthly(): array
    {
        return $this->forRange(
            DateRange::thisMonth(),
            'monthly_revenue_goal',
            5000.0,
            Date::now()->format('F Y'),
        );
    }

    /** @return array{label: string, goal: float, revenue: float, percentage: float|int} */
    public function yearly(): array
    {
        return $this->forRange(
            DateRange::thisYear(),
            'yearly_revenue_goal',
            50000.0,
            Date::now()->format('Y'),
        );
    }

    /** @return array{label: string, goal: float, revenue: float, percentage: float|int} */
    private function forRange(DateRange $range, string $setting, float $default, string $label): array
    {
        $storedGoal = $this->settings->get($setting, $default);
        $goal = is_numeric($storedGoal) ? (float) $storedGoal : $default;
        $revenue = (float) ((int) Order::query()
            ->whereBetween('created_at', $range->toArray())
            ->active()
            ->sum('total') / 100);

        return [
            'label' => $label,
            'goal' => $goal,
            'revenue' => $revenue,
            'percentage' => $goal > 0 ? min(round($revenue / $goal * 100, 1), 100) : 0,
        ];
    }
}
