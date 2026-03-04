<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OrderCalendar extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static string $navigationLabel = 'Order Calendar';
    protected static string|\UnitEnum|null $navigationGroup = 'Sales';
    protected string $view = 'filament.pages.order-calendar';

    public int $currentYear;
    public int $currentMonth;
    public Collection $orderCounts;
    public Collection $selectedDayOrders;
    public ?string $selectedDate = null;

    public function mount()
    {
        $this->currentYear = now()->year;
        $this->currentMonth = now()->month;
        $this->selectedDayOrders = collect();
        $this->loadOrderCounts();
    }

    public function loadOrderCounts()
    {
        $startOfMonth = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $this->orderCounts = Order::whereBetween('requested_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('DATE(requested_date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');
    }

    public function previousMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentYear = $date->year;
        $this->currentMonth = $date->month;
        $this->selectedDate = null;
        $this->selectedDayOrders = collect();
        $this->loadOrderCounts();
    }

    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentYear = $date->year;
        $this->currentMonth = $date->month;
        $this->selectedDate = null;
        $this->selectedDayOrders = collect();
        $this->loadOrderCounts();
    }

    public function selectDay(string $date)
    {
        $this->selectedDate = $date;
        $this->selectedDayOrders = Order::with(['customer', 'orderItems.product'])
            ->whereDate('requested_date', $date)
            ->orderBy('requested_time')
            ->get();
    }

    public function getCalendarDays(): Collection
    {
        $startOfMonth = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1);
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
        if ($count === 0) return 'bg-gray-100 hover:bg-gray-200';
        if ($count <= 5) return 'bg-green-100 hover:bg-green-200 text-green-800';
        if ($count <= 10) return 'bg-yellow-100 hover:bg-yellow-200 text-yellow-800';
        return 'bg-red-100 hover:bg-red-200 text-red-800';
    }

    public function getCurrentMonthName(): string
    {
        return Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->format('F Y');
    }
}