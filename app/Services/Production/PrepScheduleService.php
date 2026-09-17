<?php

namespace App\Services\Production;

use App\DataTransferObjects\Production\PrepTask;
use App\DataTransferObjects\Production\PrepTimelineItem;
use App\DataTransferObjects\Production\PrepWeekSummary;
use App\DataTransferObjects\Production\ProductPreparationSummary;
use App\DataTransferObjects\Production\WeeklyPrepData;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

/**
 * @phpstan-type WeeklyOrders Collection<string, EloquentCollection<int, Order>>
 * @phpstan-type PrepSchedule Collection<string, Collection<int, PrepTask>>
 */
class PrepScheduleService
{
    /**
     * Load orders for the given week and generate day list.
     */
    public function loadWeeklyData(string $weekStart): WeeklyPrepData
    {
        $startDate = Date::parse($weekStart);
        $endDate = $startDate->copy()->endOfWeek();

        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $weekDays[] = $startDate->copy()->addDays($i);
        }

        $weeklyOrders = collect(Order::with(['customer', 'orderItems.product.recipes'])
            ->whereBetween('delivery_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->oldest('delivery_date')
            ->orderBy('delivery_time')
            ->get()
            ->groupBy(fn (Order $order) => Date::parse($order->delivery_date)->format('Y-m-d'))
            ->all());

        $prepSchedule = $this->generatePrepSchedule($weeklyOrders);

        return new WeeklyPrepData($weeklyOrders, $weekDays, $prepSchedule);
    }

    /**
     * @param  WeeklyOrders  $weeklyOrders
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

                        $prepTasks[] = new PrepTask(
                            date: $date,
                            orderNumber: $order->order_number,
                            customerName: $order->customer->name ?? 'Unknown Customer',
                            productName: $product->name,
                            recipeName: $recipe->name,
                            quantity: $quantity,
                            prepTimeMinutes: $prepTimeMinutes,
                            deliveryTime: $order->delivery_time ? Date::parse($order->delivery_time)->format('H:i') : 'Not specified',
                            prepStartTime: $prepStartTime->format('H:i'),
                            prepStartDateTime: $prepStartTime,
                        );
                    }
                }
            }
        }

        return collect($prepTasks)->groupBy('date');
    }

    /**
     * @param  WeeklyOrders  $weeklyOrders
     * @return Collection<string, ProductPreparationSummary>
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
                        $productSummary[$productName] = new ProductPreparationSummary(
                            productName: $existing->productName,
                            totalQuantity: $existing->totalQuantity + $quantity,
                            ordersCount: $existing->ordersCount + 1,
                        );
                    } else {
                        $productSummary[$productName] = new ProductPreparationSummary($productName, $quantity, 1);
                    }
                }
            }
        }

        return collect($productSummary)->sortByDesc('totalQuantity');
    }

    /**
     * @param  PrepSchedule  $prepSchedule
     * @return Collection<string, Collection<int, PrepTimelineItem>>
     */
    public function getTimelineView(Collection $prepSchedule): Collection
    {
        $timeline = [];

        foreach ($prepSchedule as $date => $prepTasks) {
            $dayTimeline = $prepTasks->sortBy('prepStartDateTime')->map(fn (PrepTask $task): PrepTimelineItem => new PrepTimelineItem(
                time: $task->prepStartTime,
                task: "Start {$task->productName} (x{$task->quantity}) for {$task->customerName}",
                duration: $task->prepTimeMinutes,
                order: $task->orderNumber,
                deliveryTime: $task->deliveryTime,
            ));

            $timeline[$date] = $dayTimeline->values();
        }

        return collect($timeline);
    }

    /**
     * @param  PrepSchedule  $prepSchedule
     */
    public function getTotalPrepHours(Collection $prepSchedule): float
    {
        $totalMinutes = 0;

        foreach ($prepSchedule as $date => $prepTasks) {
            $totalMinutes += $prepTasks->sum(
                fn (PrepTask $task): int => $task->prepTimeMinutes,
            );
        }

        return round($totalMinutes / 60, 1);
    }

    /**
     * @param  WeeklyOrders  $weeklyOrders
     * @param  PrepSchedule  $prepSchedule
     */
    public function getWeekSummary(Collection $weeklyOrders, Collection $prepSchedule): PrepWeekSummary
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

        return new PrepWeekSummary(
            totalOrders: $totalOrders,
            totalItems: $totalItems,
            totalRevenue: $totalRevenue,
            totalPrepHours: $this->getTotalPrepHours($prepSchedule),
        );
    }
}
