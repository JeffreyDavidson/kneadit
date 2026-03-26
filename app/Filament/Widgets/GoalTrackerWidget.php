<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\ValueObjects\DateRange;
use Filament\Widgets\Widget;

class GoalTrackerWidget extends Widget
{
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
        $this->editingGoal = settings($key, $default);
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
    }

    public function saveGoal(): void
    {
        $key = $this->editingType === 'monthly' ? 'monthly_revenue_goal' : 'yearly_revenue_goal';
        settings([$key => $this->editingGoal]);
        $this->showEditModal = false;
    }

    /** @return array<string, mixed> */
    public function getMonthlyDataProperty(): array
    {
        $goal = (float) settings('monthly_revenue_goal', 5000);
        $range = DateRange::thisMonth();

        $revenue = (float) Order::query()->whereBetween('created_at', $range->toArray())
            ->whereNotIn('status', [OrderStatus::Cancelled])
            ->sum('total');

        $percentage = $goal > 0 ? min(round($revenue / $goal * 100, 1), 100) : 0;

        return [
            'label' => now()->format('F Y'),
            'goal' => $goal,
            'revenue' => $revenue,
            'percentage' => $percentage,
        ];
    }

    /** @return array<string, mixed> */
    public function getYearlyDataProperty(): array
    {
        $goal = (float) settings('yearly_revenue_goal', 50000);
        $range = DateRange::thisYear();

        $revenue = (float) Order::query()->whereBetween('created_at', $range->toArray())
            ->whereNotIn('status', [OrderStatus::Cancelled])
            ->sum('total');

        $percentage = $goal > 0 ? min(round($revenue / $goal * 100, 1), 100) : 0;

        return [
            'label' => now()->format('Y'),
            'goal' => $goal,
            'revenue' => $revenue,
            'percentage' => $percentage,
        ];
    }
}
