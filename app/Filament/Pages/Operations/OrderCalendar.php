<?php

namespace App\Filament\Pages\Operations;

use App\DataTransferObjects\Orders\OrderCalendarDay;
use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Orders\Order;
use App\Queries\Orders\OrderCalendarQuery;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Laravel\Pennant\Feature;

class OrderCalendar extends Page
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

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Order Calendar';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.operations.order-calendar';

    public int $currentYear;

    public int $currentMonth;

    /** @var Collection<string, int> */
    public Collection $orderCounts;

    /** @var Collection<int, Order> */
    public Collection $selectedDayOrders;

    public ?string $selectedDate = null;

    public function mount(): void
    {
        $this->currentYear = now()->year;
        $this->currentMonth = now()->month;
        $this->selectedDayOrders = (new Order)->newCollection();
        $this->loadOrderCounts();
    }

    public function loadOrderCounts(): void
    {
        $month = Date::createFromDate($this->currentYear, $this->currentMonth, 1);

        $this->orderCounts = OrderCalendarQuery::countsForMonth($month);
    }

    public function previousMonth(): void
    {
        $date = Date::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentYear = $date->year;
        $this->currentMonth = $date->month;
        $this->selectedDate = null;
        $this->selectedDayOrders = (new Order)->newCollection();
        $this->loadOrderCounts();
    }

    public function nextMonth(): void
    {
        $date = Date::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentYear = $date->year;
        $this->currentMonth = $date->month;
        $this->selectedDate = null;
        $this->selectedDayOrders = (new Order)->newCollection();
        $this->loadOrderCounts();
    }

    public function selectDay(string $date): void
    {
        $this->selectedDate = $date;
        $this->selectedDayOrders = OrderCalendarQuery::ordersForDate($date);
    }

    /** @return Collection<int, mixed> */
    public function getCalendarDays(): Collection
    {
        $startOfMonth = Date::createFromDate($this->currentYear, $this->currentMonth, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $startOfCalendar = $startOfMonth->copy()->startOfWeek();
        $endOfCalendar = $endOfMonth->copy()->endOfWeek();

        $days = collect();
        $current = $startOfCalendar->copy();

        while ($current->lte($endOfCalendar)) {
            $dateString = $current->format('Y-m-d');
            $orderCount = $this->orderCounts->get($dateString, 0);

            $days->push((new OrderCalendarDay(
                date: $current->copy(),
                displayMonth: $this->currentMonth,
                orderCount: $orderCount,
            ))->toArray());

            $current->addDay();
        }

        return $days;
    }

    public function getCurrentMonthName(): string
    {
        return Date::createFromDate($this->currentYear, $this->currentMonth, 1)->format('F Y');
    }
}
