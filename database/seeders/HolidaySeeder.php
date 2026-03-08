<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Holiday;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = [
            [
                'name' => "Valentine's Day",
                'date' => '2026-02-14',
                'lead_days' => 10,
                'notes' => 'Popular day for romantic treats and red velvet items',
            ],
            [
                'name' => 'Easter',
                'date' => '2026-04-05',
                'lead_days' => 14,
                'notes' => 'Easter cakes, cross buns, and spring-themed pastries',
            ],
            [
                'name' => "Mother's Day",
                'date' => '2026-05-10',
                'lead_days' => 7,
                'notes' => 'Special cakes and treats for mothers',
            ],
            [
                'name' => "Father's Day",
                'date' => '2026-06-21',
                'lead_days' => 7,
                'notes' => 'Father-themed treats and special orders',
            ],
            [
                'name' => '4th of July',
                'date' => '2026-07-04',
                'lead_days' => 7,
                'notes' => 'Patriotic red, white, and blue themed items',
            ],
            [
                'name' => 'Halloween',
                'date' => '2026-10-31',
                'lead_days' => 10,
                'notes' => 'Spooky treats, orange and black themed items',
            ],
            [
                'name' => 'Thanksgiving',
                'date' => '2026-11-26',
                'lead_days' => 14,
                'notes' => 'Pies, turkey-shaped cookies, autumn themed items',
            ],
            [
                'name' => 'Christmas',
                'date' => '2026-12-25',
                'lead_days' => 21,
                'notes' => 'Christmas cookies, gingerbread, holiday cakes and treats',
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                ['name' => $holiday['name'], 'date' => $holiday['date']],
                $holiday
            );
        }
    }
}
