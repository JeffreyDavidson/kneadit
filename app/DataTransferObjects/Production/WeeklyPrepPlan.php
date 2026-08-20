<?php

namespace App\DataTransferObjects\Production;

use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final readonly class WeeklyPrepPlan
{
    /**
     * @param Collection<string, EloquentCollection<int, Order>> $weeklyOrders
     * @param list<Carbon> $weekDays
     * @param Collection<string, Collection<int, array{date: string, order_number: string, customer_name: string, product_name: string, recipe_name: string, quantity: int, prep_time_minutes: int, delivery_time: string, prep_start_time: string, prep_start_datetime: Carbon}>> $prepSchedule
     */
    public function __construct(
        public Collection $weeklyOrders,
        public array $weekDays,
        public Collection $prepSchedule,
    ) {}
}
