<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Setting;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class GoalTrackerWidget extends Widget
{
    protected string $view = 'filament.widgets.goal-tracker';

    protected int | string | array $columnSpan = 'full';

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
        $this->editingGoal = Setting::get($key, $default);
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
    }

    public function saveGoal(): void
    {
        $key = $this->editingType === 'monthly' ? 'monthly_revenue_goal' : 'yearly_revenue_goal';
        Setting::set($key, $this->editingGoal);
        $this->showEditModal = false;
    }

    public function getMonthlyDataProperty(): array
    {
        $goal = (float) Setting::get('monthly_revenue_goal', 5000);
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $revenue = (float) Order::whereBetween('created_at', [$start, $end])
            ->whereNotIn('status', ['cancelled'])
            ->sum('total');

        $percentage = $goal > 0 ? min(round($revenue / $goal * 100, 1), 100) : 0;

        return [
            'label' => now()->format('F Y'),
            'goal' => $goal,
            'revenue' => $revenue,
            'percentage' => $percentage,
        ];
    }

    public function getYearlyDataProperty(): array
    {
        $goal = (float) Setting::get('yearly_revenue_goal', 50000);
        $start = Carbon::now()->startOfYear();
        $end = Carbon::now()->endOfYear();

        $revenue = (float) Order::whereBetween('created_at', [$start, $end])
            ->whereNotIn('status', ['cancelled'])
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
