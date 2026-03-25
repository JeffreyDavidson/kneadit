<?php

namespace App\Filament\Pages;

use App\Enums\SubscriptionTier;
use App\Enums\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Order;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
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

    public Collection $weeklyOrders;

    public Collection $prepSchedule;

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

        $startDate = Date::parse($this->selectedWeekStart);
        $endDate = $startDate->copy()->endOfWeek();

        // Generate week days
        $this->weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $this->weekDays[] = $startDate->copy()->addDays($i);
        }

        // Load orders for the week
        $this->weeklyOrders = Order::with(['customer', 'orderItems.product.recipes'])
            ->whereBetween('delivery_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->oldest('delivery_date')
            ->orderBy('delivery_time')
            ->get()
            ->groupBy(function (Order $order) {
                return Date::parse($order->delivery_date)->format('Y-m-d');
            });

        $this->generatePrepSchedule();
    }

    private function generatePrepSchedule(): void
    {
        $prepTasks = collect();

        foreach ($this->weeklyOrders as $date => $orders) {
            foreach ($orders as $order) {
                foreach ($order->orderItems as $orderItem) {
                    $product = $orderItem->product;

                    if ($product && $product->recipes->isNotEmpty()) {
                        $recipe = $product->recipes->first();
                        $quantity = $orderItem->quantity;
                        $prepTimeMinutes = $recipe->prep_time_minutes ?? 60; // Default 1 hour if not set

                        // Calculate when to start prep
                        $requestedDateTime = Date::parse($order->delivery_date);
                        if ($order->delivery_time) {
                            $requestedDateTime->setTimeFromTimeString($order->delivery_time);
                        }

                        $prepStartTime = $requestedDateTime->copy()->subMinutes($prepTimeMinutes);

                        $prepTasks->push([
                            'date' => $date,
                            'order_number' => $order->order_number,
                            'customer_name' => $order->customer->name ?? 'Unknown Customer',
                            'product_name' => $product->name,
                            'recipe_name' => $recipe->name,
                            'quantity' => $quantity,
                            'prep_time_minutes' => $prepTimeMinutes,
                            'delivery_time' => $order->delivery_time ? Date::parse($order->delivery_time)->format('H:i') : 'Not specified',
                            'prep_start_time' => $prepStartTime->format('H:i'),
                            'prep_start_datetime' => $prepStartTime,
                        ]);
                    }
                }
            }
        }

        $this->prepSchedule = $prepTasks->groupBy('date');
    }

    public function getProductSummary(): Collection
    {
        $productSummary = collect();

        foreach ($this->weeklyOrders as $date => $orders) {
            foreach ($orders as $order) {
                foreach ($order->orderItems as $orderItem) {
                    $productName = $orderItem->product->name ?? 'Unknown Product';
                    $quantity = $orderItem->quantity;

                    if ($productSummary->has($productName)) {
                        $productSummary[$productName]['total_quantity'] += $quantity;
                        $productSummary[$productName]['orders_count'] += 1;
                    } else {
                        $productSummary[$productName] = [
                            'product_name' => $productName,
                            'total_quantity' => $quantity,
                            'orders_count' => 1,
                        ];
                    }
                }
            }
        }

        return $productSummary->sortByDesc('total_quantity');
    }

    public function getTimelineView(): Collection
    {
        $timeline = collect();

        foreach ($this->prepSchedule as $date => $prepTasks) {
            $dayTimeline = $prepTasks->sortBy('prep_start_datetime')->map(function (array $task) {
                return [
                    'time' => $task['prep_start_time'],
                    'task' => "Start {$task['product_name']} (x{$task['quantity']}) for {$task['customer_name']}",
                    'duration' => $task['prep_time_minutes'],
                    'order' => $task['order_number'],
                    'delivery_time' => $task['delivery_time'],
                ];
            });

            $timeline[$date] = $dayTimeline;
        }

        return $timeline;
    }

    public function getTotalPrepHours(): float
    {
        $totalMinutes = 0;

        foreach ($this->prepSchedule as $date => $prepTasks) {
            $totalMinutes += $prepTasks->sum('prep_time_minutes');
        }

        return round($totalMinutes / 60, 1);
    }

    public function getWeekSummary(): array
    {
        $totalOrders = 0;
        $totalItems = 0;
        $totalRevenue = 0;

        foreach ($this->weeklyOrders as $date => $orders) {
            $totalOrders += $orders->count();
            foreach ($orders as $order) {
                $totalItems += $order->orderItems->sum('quantity');
                $totalRevenue += $order->total;
            }
        }

        return [
            'total_orders' => $totalOrders,
            'total_items' => $totalItems,
            'total_revenue' => $totalRevenue,
            'total_prep_hours' => $this->getTotalPrepHours(),
        ];
    }
}
