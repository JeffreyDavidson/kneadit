<?php

namespace Database\Seeders;

use App\Models\CapacityLimit;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CapacityLimitSeeder extends Seeder
{
    public function run(): void
    {
        $capacityLimits = [
            // Valentine's Day - very busy, reduced capacity
            [
                'date' => Carbon::now()->addDays(2),
                'max_orders' => 8,
            ],
            // Weekend before a holiday - high demand
            [
                'date' => Carbon::now()->addDays(5),
                'max_orders' => 10,
            ],
            // Regular busy weekend
            [
                'date' => Carbon::now()->addDays(7),
                'max_orders' => 12,
            ],
            // Mother's Day weekend - completely booked
            [
                'date' => Carbon::now()->addDays(9),
                'max_orders' => 6,
            ],
            // Easter weekend - high demand
            [
                'date' => Carbon::now()->addDays(12),
                'max_orders' => 8,
            ],
            // Regular weekend with slightly reduced capacity
            [
                'date' => Carbon::now()->addDays(14),
                'max_orders' => 13,
            ],
        ];

        foreach ($capacityLimits as $limitData) {
            CapacityLimit::updateOrCreate(
                ['date' => $limitData['date']],
                $limitData
            );
        }
    }
}