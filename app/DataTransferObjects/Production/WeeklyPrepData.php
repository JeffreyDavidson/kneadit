<?php

namespace App\DataTransferObjects\Production;

use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final readonly class WeeklyPrepData
{
    /**
     * @param  Collection<string, EloquentCollection<int, Order>>  $weeklyOrders
     * @param  list<Carbon>  $weekDays
     * @param  Collection<string, Collection<int, PrepTask>>  $prepSchedule
     */
    public function __construct(
        public Collection $weeklyOrders,
        public array $weekDays,
        public Collection $prepSchedule,
    ) {}
}
