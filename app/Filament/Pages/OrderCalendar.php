<?php

namespace App\Filament\Pages;

use App\Enums\SubscriptionTier;
use App\Enums\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Order;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Laravel\Pennant\Feature;

class OrderCalendar extends Page
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

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Order Calendar';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.order-calendar';

    public int $currentYear;

    public int $currentMonth;

    /** @var Collection<int, mixed> */
    public Collection $orderCounts;

    /** @var Collection<int, mixed> */
    public Collection $selectedDayOrders;

    public ?string $selectedDate = null;

    public function mount(): void
    {
        $this->currentYear = now()->year;
        $this->currentMonth = now()->month;
        $this->selectedDayOrders = collect();
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
        $this->selectedDayOrders = collect();
        $this->loadOrderCounts();
    }

    public function nextMonth(): void
    {
        $date = Date::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentYear = $date->year;
        $this->currentMonth = $date->month;
        $this->selectedDate = null;
        $this->selectedDayOrders = collect();
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

        $days = collect();
        $current = $startOfCalendar->copy();

        while ($current->lte($endOfCalendar)) {
            $dateString = $current->format('Y-m-d');
            $orderCount = $this->orderCounts->get($dateString, 0);

            $days->push([
                'date' => $current->copy(),
                'dateString' => $dateString,
                'isCurrentMonth' => $current->month === $this->currentMonth,
                'isToday' => $current->isToday(),
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
            return 'bg-gray-100 hover:bg-gray-200';
        }
        if ($count <= 5) {
            return 'bg-green-100 hover:bg-green-200 text-green-800';
        }
        if ($count <= 10) {
            return 'bg-yellow-100 hover:bg-yellow-200 text-yellow-800';
        }

        return 'bg-red-100 hover:bg-red-200 text-red-800';
    }

    public function getCurrentMonthName(): string
    {
        return Date::createFromDate($this->currentYear, $this->currentMonth, 1)->format('F Y');
    }
}
