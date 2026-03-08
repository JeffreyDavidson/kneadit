<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Traits\RequiresRole;
use App\Models\Holiday;
use Illuminate\Support\Collection;

use App\Traits\HasPlanGating;
class HolidayPlanningCalendar extends Page
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
    }

    protected static string $requiredPlan = 'pro';
    protected static bool $shouldRegisterNavigation = false;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationLabel = 'Holidays';
    protected static string|\UnitEnum|null $navigationGroup = 'Tools';
    protected static ?int $navigationSort = 9;
    protected string $view = 'filament.pages.holiday-planning-calendar';

    public Collection $holidays;
    public Collection $upcomingHolidays;
    public Collection $inPrepPeriod;

    public function mount()
    {
        $this->loadHolidays();
    }

    public function loadHolidays()
    {
        $this->holidays = Holiday::orderBy('date')->get();
        
        $this->upcomingHolidays = $this->holidays
            ->filter(fn($holiday) => $holiday->is_upcoming)
            ->take(10);
            
        $this->inPrepPeriod = $this->holidays
            ->filter(fn($holiday) => $holiday->is_in_prep_period);
    }

    public function getHolidaysByMonth(): Collection
    {
        $currentYear = now()->year;
        $nextYear = $currentYear + 1;
        
        return $this->holidays
            ->filter(function ($holiday) use ($currentYear, $nextYear) {
                $year = $holiday->date->year;
                return $year === $currentYear || $year === $nextYear;
            })
            ->groupBy(function ($holiday) {
                return $holiday->date->format('Y-m');
            })
            ->sortKeys();
    }

    public function getDaysAway(Holiday $holiday): string
    {
        $daysAway = $holiday->days_away;
        
        if ($daysAway < 0) {
            return 'Passed ' . abs($daysAway) . ' days ago';
        } elseif ($daysAway === 0) {
            return 'Today!';
        } elseif ($daysAway === 1) {
            return 'Tomorrow';
        } else {
            return $daysAway . ' days away';
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