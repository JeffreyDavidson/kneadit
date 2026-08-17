<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Models\Orders\Order;
use App\Services\Settings\SettingsManager;
use App\Support\DatabaseValue;
use App\ValueObjects\DateRange;
use Filament\Widgets\Widget;

class GoalTrackerWidget extends Widget
{
    use CachesWidgetData;

    protected string $view = 'filament.widgets.goal-tracker';

    protected int|string|array $columnSpan = 'full';

    public bool $showEditModal = false;

    public string $newGoal = '';

    public string $editingGoal = '';

    public string $editingType = '';

    public function mount(): void {}

    public function openEditModal(string $type): void
    {
        $this->editingType = $type;
        $key = $type === 'monthly' ? 'monthly_revenue_goal' : 'yearly_revenue_goal';
        $default = $type === 'monthly' ? '5000' : '50000';
        $this->editingGoal = DatabaseValue::nullableString(resolve(SettingsManager::class)->get($key, $default)) ?? $default;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
    }

    public function saveGoal(): void
    {
        $key = $this->editingType === 'monthly' ? 'monthly_revenue_goal' : 'yearly_revenue_goal';
        resolve(SettingsManager::class)->set($key, $this->editingGoal);
        $this->showEditModal = false;
    }

    /** @return array<string, mixed> */
    public function getMonthlyDataProperty(): array
    {
        return $this->cached('monthly_' . now()->format('Y-m'), [900, 1800], function (): array {
            $goal = DatabaseValue::float(resolve(SettingsManager::class)->get('monthly_revenue_goal'), 5000);
            $range = DateRange::thisMonth();

            // orders.total is bigint cents (migration 2026_04_22_201500).
            $revenue = DatabaseValue::int(Order::query()->whereBetween('created_at', $range->toArray())
                ->active()
                ->sum('total')) / 100;

            $percentage = $goal > 0 ? min(round($revenue / $goal * 100, 1), 100) : 0;

            return [
                'label' => now()->format('F Y'),
                'goal' => $goal,
                'revenue' => $revenue,
                'percentage' => $percentage,
            ];
        });
    }

    /** @return array<string, mixed> */
    public function getYearlyDataProperty(): array
    {
        return $this->cached('yearly_' . now()->format('Y'), [1800, 3600], function (): array {
            $goal = DatabaseValue::float(resolve(SettingsManager::class)->get('yearly_revenue_goal'), 50000);
            $range = DateRange::thisYear();

            // orders.total is bigint cents (migration 2026_04_22_201500).
            $revenue = DatabaseValue::int(Order::query()->whereBetween('created_at', $range->toArray())
                ->active()
                ->sum('total')) / 100;

            $percentage = $goal > 0 ? min(round($revenue / $goal * 100, 1), 100) : 0;

            return [
                'label' => now()->format('Y'),
                'goal' => $goal,
                'revenue' => $revenue,
                'percentage' => $percentage,
            ];
        });
    }

    protected function cachePrefix(): string
    {
        return 'goal_tracker';
    }
}
