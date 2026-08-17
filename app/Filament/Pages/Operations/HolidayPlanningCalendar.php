<?php

namespace App\Filament\Pages\Operations;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Operations\Holiday;
use App\Presenters\HolidayPresenter;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Laravel\Pennant\Feature;

class HolidayPlanningCalendar extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static ?string $navigationLabel = 'Holidays';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 9;

    protected string $view = 'filament.pages.operations.holiday-planning-calendar';

    /** @var Collection<int, Holiday> */
    public Collection $holidays;

    /** @var Collection<int, Holiday> */
    public Collection $upcomingHolidays;

    /** @var Collection<int, Holiday> */
    public Collection $inPrepPeriod;

    public function mount(): void
    {
        $this->loadHolidays();
    }

    public function loadHolidays(): void
    {
        $this->holidays = Holiday::query()->orderBy('date')->get();

        $this->upcomingHolidays = $this->holidays
            ->filter(fn (Holiday $holiday) => HolidayPresenter::for($holiday)->isUpcoming())
            ->take(10);

        $this->inPrepPeriod = $this->holidays
            ->filter(fn (Holiday $holiday) => HolidayPresenter::for($holiday)->isInPrepPeriod());
    }

    /** @return Collection<string, Collection<int, Holiday>> */
    public function getHolidaysByMonth(): Collection
    {
        $currentYear = now()->year;
        $nextYear = $currentYear + 1;

        return $this->holidays
            ->filter(function (Holiday $holiday) use ($currentYear, $nextYear) {
                $year = $holiday->date->year;

                return $year === $currentYear || $year === $nextYear;
            })
            ->groupBy(function (Holiday $holiday) {
                return $holiday->date->format('Y-m');
            })
            ->sortKeys();
    }
}
