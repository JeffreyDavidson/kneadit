<?php

namespace App\Filament\Pages;

use App\Enums\SubscriptionTier;
use App\Enums\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Holiday;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Pennant\Feature;

class HolidayPlanningCalendar extends Page
{
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    protected static bool $shouldRegisterNavigation = false;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationLabel = 'Holidays';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 9;

    protected string $view = 'filament.pages.holiday-planning-calendar';

    /** @var Collection<int, mixed> */
    public Collection $holidays;

    /** @var Collection<int, mixed> */
    public Collection $upcomingHolidays;

    /** @var Collection<int, mixed> */
    public Collection $inPrepPeriod;

    public function mount(): void
    {
        $this->loadHolidays();
    }

    public function loadHolidays(): void
    {
        $this->holidays = Holiday::query()->orderBy('date')->get();

        $this->upcomingHolidays = $this->holidays
            ->filter(fn (Holiday $holiday) => $holiday->is_upcoming)
            ->take(10);

        $this->inPrepPeriod = $this->holidays
            ->filter(fn (Holiday $holiday) => $holiday->is_in_prep_period);
    }

    /** @return Collection<int, mixed> */
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

    public function getDaysAway(Holiday $holiday): string
    {
        $daysAway = $holiday->days_away;

        if ($daysAway < 0) {
            return 'Passed '.abs($daysAway).' days ago';
        } elseif ($daysAway === 0) {
            return 'Today!';
        } elseif ($daysAway === 1) {
            return 'Tomorrow';
        } else {
            return $daysAway.' days away';
        }
    }

    public function getStatusColor(Holiday $holiday): string
    {
        $daysAway = $holiday->days_away;

        if ($daysAway < 0) {
            return 'gray'; // Passed
        } elseif ($holiday->is_in_prep_period) {
            return 'yellow'; // In prep period
        } elseif ($daysAway <= $holiday->lead_days) {
            return 'red'; // Urgent - should have started prep
        } elseif ($daysAway <= $holiday->lead_days * 2) {
            return 'orange'; // Coming up soon
        } else {
            return 'green'; // Plenty of time
        }
    }

    public function getStatusText(Holiday $holiday): string
    {
        $daysAway = $holiday->days_away;

        if ($daysAway < 0) {
            return 'Passed';
        } elseif ($holiday->is_in_prep_period) {
            return 'Prep Time!';
        } elseif ($daysAway <= $holiday->lead_days) {
            return 'Urgent';
        } elseif ($daysAway <= $holiday->lead_days * 2) {
            return 'Coming Up';
        } else {
            return 'Planning';
        }
    }
}
