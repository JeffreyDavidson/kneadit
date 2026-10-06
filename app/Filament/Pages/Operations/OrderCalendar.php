<?php

namespace App\Filament\Pages\Operations;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Orders\Order;
use App\Services\Scheduling\BakeryClock;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Laravel\Pennant\Feature;

class OrderCalendar extends Page
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
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    #[\Override]
    protected static ?string $navigationLabel = 'Order Calendar';

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    #[\Override]
    protected static ?int $navigationSort = 2;

    #[\Override]
    protected string $view = 'filament.pages.operations.order-calendar';

    public int $currentYear;

    public int $currentMonth;

    /** @var Collection<string, mixed> */
    public Collection $orderCounts;

    /** @var Collection<int, Order> */
    public Collection $selectedDayOrders;

    public ?string $selectedDate = null;

    public function mount(): void
    {
        $today = resolve(BakeryClock::class)->today();
        $this->currentYear = $today->year;
        $this->currentMonth = $today->month;
        $this->selectedDayOrders = (new Order)->newCollection();
        $this->loadOrderCounts();
    }

    public function loadOrderCounts(): void
    {
        $startOfMonth = Date::createFromDate($this->currentYear, $this->currentMonth, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $this->orderCounts = Order::query()->whereBetween('delivery_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('DATE(delivery_date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');
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
        $this->selectedDayOrders = Order::with(['customer', 'orderItems.product'])
            ->whereDate('delivery_date', $date)
            ->orderBy('delivery_time')
            ->get();
    }

    /** @return Collection<int, mixed> */
    public function getCalendarDays(): Collection
    {
        $startOfMonth = Date::createFromDate($this->currentYear, $this->currentMonth, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $startOfCalendar = $startOfMonth->copy()->startOfWeek();
        $endOfCalendar = $endOfMonth->copy()->endOfWeek();
        $today = resolve(BakeryClock::class)->today();

        $days = collect();
        $current = $startOfCalendar->copy();

        while ($current->lte($endOfCalendar)) {
            $dateString = $current->format('Y-m-d');
            $orderCount = filter_var($this->orderCounts->get($dateString, 0), FILTER_VALIDATE_INT);
            $orderCount = is_int($orderCount) ? $orderCount : 0;

            $days->push([
                'date' => $current->copy(),
                'dateString' => $dateString,
                'isCurrentMonth' => $current->month === $this->currentMonth,
                'isToday' => $current->isSameDay($today),
                'orderCount' => $orderCount,
                'colorClass' => $this->getColorClass($orderCount),
            ]);

            $current->addDay();
        }

        return $days;
    }

    private function getColorClass(int $count): string
    {
        if ($count === 0) {
            return 'bg-(--kn-surface-sunken) text-(--kn-ink) hover:brightness-95';
        }
        if ($count <= 5) {
            return 'bg-(--kn-success-tint) text-(--kn-success) hover:brightness-95';
        }
        if ($count <= 10) {
            return 'bg-(--kn-warning-tint) text-(--kn-warning) hover:brightness-95';
        }

        return 'bg-(--kn-danger-tint) text-(--kn-danger) hover:brightness-95';
    }

    public function getCurrentMonthName(): string
    {
        return Date::createFromDate($this->currentYear, $this->currentMonth, 1)->format('F Y');
    }
}
