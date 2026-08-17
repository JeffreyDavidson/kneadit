<?php

namespace App\Filament\Pages\Operations;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Orders\Order;
use App\Services\Production\PrepScheduleService;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Pennant\Feature;

/**
 * @phpstan-type WeeklyOrders Collection<string, EloquentCollection<int, Order>>
 * @phpstan-type PrepTask array{date: string, order_number: string, customer_name: string, product_name: string, recipe_name: string, quantity: int, prep_time_minutes: int, delivery_time: string, prep_start_time: string, prep_start_datetime: Carbon}
 * @phpstan-type PrepSchedule Collection<string, Collection<int, PrepTask>>
 */
class WeeklyPrepPlanner extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('growth-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Prep Planner';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.operations.weekly-prep-planner';

    public ?string $selectedWeekStart = null;

    /** @var WeeklyOrders */
    public Collection $weeklyOrders;

    /** @var PrepSchedule */
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

    /** @return Collection<string, array{product_name: string, total_quantity: int, orders_count: int}> */
    public function getProductSummary(): Collection
    {
        return resolve(PrepScheduleService::class)->getProductSummary($this->weeklyOrders);
    }

    /** @return Collection<string, Collection<int, array{time: string, task: string, duration: int, order: string, delivery_time: string}>> */
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
