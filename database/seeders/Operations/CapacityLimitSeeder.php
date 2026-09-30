<?php

namespace Database\Seeders\Operations;

use App\Models\Operations\CapacityLimit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;

class CapacityLimitSeeder extends Seeder
{
    public function run(): void
    {
        $capacityLimits = [
            // Valentine's Day - very busy, reduced capacity
            [
                'specific_date' => Date::today()->addDays(2),
                'max_orders' => 8,
            ],
            // Weekend before a holiday - high demand
            [
                'specific_date' => Date::today()->addDays(5),
                'max_orders' => 10,
            ],
            // Regular busy weekend
            [
                'specific_date' => Date::today()->addDays(7),
                'max_orders' => 12,
            ],
            // Mother's Day weekend - completely booked
            [
                'specific_date' => Date::today()->addDays(9),
                'max_orders' => 6,
            ],
            // Easter weekend - high demand
            [
                'specific_date' => Date::today()->addDays(12),
                'max_orders' => 8,
            ],
            // Regular weekend with slightly reduced capacity
            [
                'specific_date' => Date::today()->addDays(14),
                'max_orders' => 13,
            ],
        ];

        foreach ($capacityLimits as $limitData) {
            CapacityLimit::query()->updateOrCreate(['specific_date' => $limitData['specific_date']], $limitData);
        }
    }
}
