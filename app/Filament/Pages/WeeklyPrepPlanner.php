<?php

namespace App\Filament\Pages;

use App\Enums\SubscriptionTier;
use App\Enums\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Services\Production\PrepScheduleService;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Pennant\Feature;

class WeeklyPrepPlanner extends Page
{
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return Feature::active('growth-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Prep Planner';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.weekly-prep-planner';

    public ?string $selectedWeekStart = null;

    /** @var Collection<int, mixed> */
    public Collection $weeklyOrders;

    /** @var Collection<int, mixed> */
    public Collection $prepSchedule;

    /** @var array<int|string, mixed> */
    public array $weekDays = [];

    public function mount(): void
    {
        $this->selectedWeekStart = now()->startOfWeek()->format('Y-m-d');
        $this->loadWeeklyData();
    }

    public function updatedSelectedWeekStart(): void
    {
        $this->loadWeeklyData();
    }

    public function loadWeeklyData(): void
    {
        if (! $this->selectedWeekStart) {
            $this->weeklyOrders = collect();
            $this->prepSchedule = collect();

            return;
        }

        $data = resolve(PrepScheduleService::class)->loadWeeklyData($this->selectedWeekStart);

        $this->weeklyOrders = $data['weeklyOrders'];
        $this->weekDays = $data['weekDays'];
        $this->prepSchedule = $data['prepSchedule'];
    }

    /** @return Collection<int, mixed> */
    public function getProductSummary(): Collection
    {
        return resolve(PrepScheduleService::class)->getProductSummary($this->weeklyOrders);
    }

    /** @return Collection<int, mixed> */
    public function getTimelineView(): Collection
    {
        return resolve(PrepScheduleService::class)->getTimelineView($this->prepSchedule);
    }

    public function getTotalPrepHours(): float
    {
        return resolve(PrepScheduleService::class)->getTotalPrepHours($this->prepSchedule);
    }

    /** @return array<string, mixed> */
    public function getWeekSummary(): array
    {
        return resolve(PrepScheduleService::class)->getWeekSummary($this->weeklyOrders, $this->prepSchedule);
    }
}
