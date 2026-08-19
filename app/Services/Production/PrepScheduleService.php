<?php

namespace App\Services\Production;

use App\DataTransferObjects\Production\WeeklyPrepPlan;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Queries\Production\WeeklyProductionOrdersQuery;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

/**
 * @phpstan-type WeeklyOrders Collection<string, EloquentCollection<int, Order>>
 * @phpstan-type PrepTask array{date: string, order_number: string, customer_name: string, product_name: string, recipe_name: string, quantity: int, prep_time_minutes: int, delivery_time: string, prep_start_time: string, prep_start_datetime: Carbon}
 * @phpstan-type PrepSchedule Collection<string, Collection<int, PrepTask>>
 */
class PrepScheduleService
{
    /**
     * Load orders for the given week and generate day list.
     */
    public function loadWeeklyData(string $weekStart): WeeklyPrepPlan
    {
        $startDate = Date::parse($weekStart);
        $endDate = $startDate->copy()->endOfWeek();

        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $weekDays[] = $startDate->copy()->addDays($i);
        }

        $weeklyOrders = collect(WeeklyProductionOrdersQuery::between($startDate, $endDate)
            ->groupBy(function (Order $order) {
                return Date::parse($order->delivery_date)->format('Y-m-d');
            })
            ->all());

        $prepSchedule = $this->generatePrepSchedule($weeklyOrders);

        return new WeeklyPrepPlan($weeklyOrders, $weekDays, $prepSchedule);
    }

    /**
     * @param WeeklyOrders $weeklyOrders
     * @return PrepSchedule
     */
    public function generatePrepSchedule(Collection $weeklyOrders): Collection
    {
        $prepTasks = [];

        foreach ($weeklyOrders as $date => $orders) {
            foreach ($orders as $order) {
                foreach ($order->orderItems as $orderItem) {
                    $product = $orderItem->product;

                    if ($product !== null && $product->recipes->isNotEmpty()) {
                        $recipe = $product->recipes->first();
                        $quantity = $orderItem->quantity;
                        $prepTimeMinutes = $recipe->prep_time_minutes ?? 60;

                        $requestedDateTime = Date::parse($order->delivery_date);
                        if ($order->delivery_time) {
                            $requestedDateTime->setTimeFromTimeString($order->delivery_time);
                        }

                        $prepStartTime = $requestedDateTime->copy()->subMinutes($prepTimeMinutes);

                        $prepTasks[] = [
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
                        ];
                    }
                }
            }
        }

        return collect($prepTasks)->groupBy('date');
    }

    /**
     * @param WeeklyOrders $weeklyOrders
     * @return Collection<string, array{product_name: string, total_quantity: int, orders_count: int}>
     */
    public function getProductSummary(Collection $weeklyOrders): Collection
    {
        $productSummary = [];

        foreach ($weeklyOrders as $date => $orders) {
            foreach ($orders as $order) {
                foreach ($order->orderItems as $orderItem) {
                    $productName = $orderItem->product->name ?? 'Unknown Product';
                    $quantity = $orderItem->quantity;

                    if (isset($productSummary[$productName])) {
                        $existing = $productSummary[$productName];
                        $existing['total_quantity'] += $quantity;
                        $existing['orders_count'] += 1;
                        $productSummary[$productName] = $existing;
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

        return collect($productSummary)->sortByDesc('total_quantity');
    }

    /**
     * @param PrepSchedule $prepSchedule
     * @return Collection<string, Collection<int, array{time: string, task: string, duration: int, order: string, delivery_time: string}>>
     */
    public function getTimelineView(Collection $prepSchedule): Collection
    {
        $timeline = [];

        foreach ($prepSchedule as $date => $prepTasks) {
            $dayTimeline = $prepTasks->sortBy('prep_start_datetime')->map(function (array $task) {
                return [
                    'time' => $task['prep_start_time'],
                    'task' => "Start {$task['product_name']} (x{$task['quantity']}) for {$task['customer_name']}",
                    'duration' => $task['prep_time_minutes'],
                    'order' => $task['order_number'],
                    'delivery_time' => $task['delivery_time'],
                ];
            });

            $timeline[$date] = $dayTimeline->values();
        }

        return collect($timeline);
    }

    /**
     * @param PrepSchedule $prepSchedule
     */
    public function getTotalPrepHours(Collection $prepSchedule): float
    {
        $totalMinutes = 0;

        foreach ($prepSchedule as $date => $prepTasks) {
            $totalMinutes += $prepTasks->sum(
                fn (array $task): int => $task['prep_time_minutes'],
            );
        }

        return round($totalMinutes / 60, 1);
    }

    /**
     * @param WeeklyOrders $weeklyOrders
     * @param PrepSchedule $prepSchedule
     * @return array{total_orders: int, total_items: int, total_revenue: float, total_prep_hours: float}
     */
    public function getWeekSummary(Collection $weeklyOrders, Collection $prepSchedule): array
    {
        $totalOrders = 0;
        $totalItems = 0;
        $totalRevenue = 0;

        foreach ($weeklyOrders as $date => $orders) {
            $totalOrders += $orders->count();
            foreach ($orders as $order) {
                $totalItems += $order->orderItems->sum(
                    fn (OrderItem $orderItem): int => $orderItem->quantity,
                );
                $totalRevenue += $order->total->dollars();
            }
        }

        return [
            'total_orders' => $totalOrders,
            'total_items' => $totalItems,
            'total_revenue' => $totalRevenue,
            'total_prep_hours' => $this->getTotalPrepHours($prepSchedule),
        ];
    }
}
