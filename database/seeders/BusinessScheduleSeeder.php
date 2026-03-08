<?php

namespace Database\Seeders;

use App\Models\BlockedDate;
use App\Models\BusinessSchedule;
use App\Models\Product;
use App\Models\SeasonalItem;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BusinessScheduleSeeder extends Seeder
{
    public function run(): void
    {
        // Weekly schedule: Mon-Fri 7am-6pm, Sat 8am-4pm, Sun closed
        $defaults = [
            0 => ['is_open' => false], // Sunday
            1 => ['is_open' => true, 'open_time' => '07:00', 'close_time' => '18:00', 'order_cutoff_time' => '14:00'],
            2 => ['is_open' => true, 'open_time' => '07:00', 'close_time' => '18:00', 'order_cutoff_time' => '14:00'],
            3 => ['is_open' => true, 'open_time' => '07:00', 'close_time' => '18:00', 'order_cutoff_time' => '14:00'],
            4 => ['is_open' => true, 'open_time' => '07:00', 'close_time' => '18:00', 'order_cutoff_time' => '14:00'],
            5 => ['is_open' => true, 'open_time' => '07:00', 'close_time' => '18:00', 'order_cutoff_time' => '14:00'],
            6 => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '16:00', 'order_cutoff_time' => '12:00'],
        ];

        foreach ($defaults as $day => $data) {
            BusinessSchedule::updateOrCreate(['day_of_week' => $day], $data);
        }

        // Blocked dates (upcoming holidays)
        $blockedDates = [
            ['date' => Carbon::parse('2026-05-25'), 'reason' => 'Holiday', 'is_all_day' => true], // Memorial Day
            ['date' => Carbon::parse('2026-07-04'), 'reason' => 'Holiday', 'is_all_day' => true], // July 4th
            ['date' => Carbon::parse('2026-09-07'), 'reason' => 'Holiday', 'is_all_day' => true], // Labor Day
            ['date' => Carbon::parse('2026-12-25'), 'reason' => 'Holiday', 'is_all_day' => true], // Christmas
        ];

        foreach ($blockedDates as $data) {
            BlockedDate::updateOrCreate(['date' => $data['date']], $data);
        }

        // Seasonal items (attach to first couple products if they exist)
        $products = Product::take(3)->get();
        if ($products->count() >= 2) {
            SeasonalItem::updateOrCreate(
                ['product_id' => $products[0]->id, 'available_from' => '2026-06-01'],
                ['available_until' => '2026-08-31', 'notes' => 'Summer special']
            );
            SeasonalItem::updateOrCreate(
                ['product_id' => $products[1]->id, 'available_from' => '2026-11-01'],
                ['available_until' => '2026-12-31', 'notes' => 'Holiday season']
            );
        }
    }
}
