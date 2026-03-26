<?php

namespace App\Services\Production;

use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

class PrepScheduleService
{
    /**
     * Load orders for the given week and generate day list.
     *
     * @return array{weeklyOrders: Collection<string, Collection<int, Order>>, weekDays: list<Carbon>, prepSchedule: Collection<string, Collection<int, array<string, mixed>>>}
     */
    public function loadWeeklyData(string $weekStart): array
    {
        $startDate = Date::parse($weekStart);
        $endDate = $startDate->copy()->endOfWeek();

        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $weekDays[] = $startDate->copy()->addDays($i);
        }

        $weeklyOrders = Order::with(['customer', 'orderItems.product.recipes'])
            ->whereBetween('delivery_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->oldest('delivery_date')
            ->orderBy('delivery_time')
            ->get()
            ->groupBy(function (Order $order) {
                return Date::parse($order->delivery_date)->format('Y-m-d');
            });

        $prepSchedule = $this->generatePrepSchedule($weeklyOrders);

        return [
            'weeklyOrders' => $weeklyOrders,
            'weekDays' => $weekDays,
            'prepSchedule' => $prepSchedule,
        ];
    }

    /**
     * @param  Collection<string, Collection<int, Order>>  $weeklyOrders
     * @return Collection<string, Collection<int, array<string, mixed>>>
     */
    public function generatePrepSchedule(Collection $weeklyOrders): Collection
    {
        $prepTasks = collect();

        foreach ($weeklyOrders as $date => $orders) {
            foreach ($orders as $order) {
                foreach ($order->orderItems as $orderItem) {
                    $product = $orderItem->product;

                    if ($product && $product->recipes->isNotEmpty()) {
                        $recipe = $product->recipes->first();
                        $quantity = $orderItem->quantity;
                        $prepTimeMinutes = $recipe->prep_time_minutes ?? 60;

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

        return $prepTasks->groupBy('date');
    }

    /**
     * @param  Collection<string, Collection<int, Order>>  $weeklyOrders
     * @return Collection<string, array<string, mixed>>
     */
    public function getProductSummary(Collection $weeklyOrders): Collection
    {
        $productSummary = collect();

        foreach ($weeklyOrders as $date => $orders) {
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

    /**
     * @param  Collection<string, Collection<int, array<string, mixed>>>  $prepSchedule
     * @return Collection<string, Collection<int, array<string, mixed>>>
     */
    public function getTimelineView(Collection $prepSchedule): Collection
    {
        $timeline = collect();

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

            $timeline[$date] = $dayTimeline;
        }

        return $timeline;
    }

    /**
     * @param  Collection<string, Collection<int, array<string, mixed>>>  $prepSchedule
     */
    public function getTotalPrepHours(Collection $prepSchedule): float
    {
        $totalMinutes = 0;

        foreach ($prepSchedule as $date => $prepTasks) {
            $totalMinutes += $prepTasks->sum('prep_time_minutes');
        }

        return round($totalMinutes / 60, 1);
    }

    /**
     * @param  Collection<string, Collection<int, Order>>  $weeklyOrders
     * @param  Collection<string, Collection<int, array<string, mixed>>>  $prepSchedule
     * @return array<string, mixed>
     */
    public function getWeekSummary(Collection $weeklyOrders, Collection $prepSchedule): array
    {
        $totalOrders = 0;
        $totalItems = 0;
        $totalRevenue = 0;

        foreach ($weeklyOrders as $date => $orders) {
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
            'total_prep_hours' => $this->getTotalPrepHours($prepSchedule),
        ];
    }
}
