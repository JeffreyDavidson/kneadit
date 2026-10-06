<?php

namespace App\Filament\Pages\Operations;

use App\DataTransferObjects\Production\PrepTimelineItem;
use App\DataTransferObjects\Production\ProductPreparationSummary;
use App\DataTransferObjects\Production\WeeklyPrepData;
use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Services\Production\PrepScheduleService;
use App\Services\Scheduling\BakeryClock;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Laravel\Pennant\Feature;
use Livewire\Attributes\Computed;

/**
 * @property-read WeeklyPrepData $weekData
 */
class WeeklyPrepPlanner extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    #[\Override]
    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('growth-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }

    #[\Override]
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    #[\Override]
    protected static ?string $navigationLabel = 'Prep Planner';

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    #[\Override]
    protected static ?int $navigationSort = 4;

    #[\Override]
    protected string $view = 'filament.pages.operations.weekly-prep-planner';

    public ?string $selectedWeekStart = null;

    public function mount(): void
    {
        $this->selectedWeekStart = resolve(BakeryClock::class)->today()->startOfWeek()->toDateString();
    }

    /**
     * Derived from the selected week on every request: the prep tasks and Carbon days are not
     * Livewire-hydratable, so they are never kept as public component state.
     */
    #[Computed]
    public function weekData(): WeeklyPrepData
    {
        if (! $this->selectedWeekStart) {
            return new WeeklyPrepData(new Collection, [], new Collection);
        }

        return resolve(PrepScheduleService::class)->loadWeeklyData($this->selectedWeekStart);
    }

    /** @return Collection<string, array{product_name: string, total_quantity: int, orders_count: int}> */
    public function getProductSummary(): Collection
    {
        return resolve(PrepScheduleService::class)->getProductSummary($this->weekData->weeklyOrders)->map(
            static fn (ProductPreparationSummary $summary): array => $summary->toArray(),
        );
    }

    /** @return Collection<string, Collection<int, array{time: string, task: string, duration: int, order: string, delivery_time: string}>> */
    public function getTimelineView(): Collection
    {
        return resolve(PrepScheduleService::class)->getTimelineView($this->weekData->prepSchedule)->map(
            static fn (Collection $items): Collection => $items->map(
                static fn (PrepTimelineItem $item): array => $item->toArray(),
            ),
        );
    }

    public function getTotalPrepHours(): float
    {
        return resolve(PrepScheduleService::class)->getTotalPrepHours($this->weekData->prepSchedule);
    }

    /** @return array{total_orders: int, total_items: int, total_revenue: float, total_prep_hours: float} */
    public function getWeekSummary(): array
    {
        return resolve(PrepScheduleService::class)->getWeekSummary($this->weekData->weeklyOrders, $this->weekData->prepSchedule)->toArray();
    }
}
