<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Queries\Financial\GoalProgressQuery;
use App\Services\Settings\SettingsManager;
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
        $goal = resolve(SettingsManager::class)->get($key, $default);
        $this->editingGoal = is_scalar($goal) ? (string) $goal : $default;
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

    /** @return array{label: string, goal: float, revenue: float, percentage: float|int} */
    public function getMonthlyDataProperty(): array
    {
        return $this->cached('monthly_' . now()->format('Y-m'), [900, 1800], fn (): array => resolve(GoalProgressQuery::class)->monthly());
    }

    /** @return array{label: string, goal: float, revenue: float, percentage: float|int} */
    public function getYearlyDataProperty(): array
    {
        return $this->cached('yearly_' . now()->format('Y'), [1800, 3600], fn (): array => resolve(GoalProgressQuery::class)->yearly());
    }

    protected function cachePrefix(): string
    {
        return 'goal_tracker';
    }
}
