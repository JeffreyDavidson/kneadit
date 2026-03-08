<?php

namespace Database\Seeders;

use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class IncomeSeeder extends Seeder
{
    public function run(): void
    {
        if (\App\Models\Income::count() > 0) {
            return;
        }

        $incomes = [
            [
                'description' => 'Winter Haven Farmers Market - Weekend Sales',
                'amount' => 485.75,
                'source' => 'farmers_market',
                'notes' => 'Strong sales - sold out of sourdough and croissants',
            ],
            [
                'description' => 'Clermont Farmers Market - Saturday Morning',
                'amount' => 312.50,
                'source' => 'farmers_market',
                'notes' => 'Good crowd, popular items were muffins and cookies',
            ],
            [
                'description' => 'Direct Cash Sale - Wedding Cake',
                'amount' => 275.00,
                'source' => 'cash_sale',
                'notes' => 'Three-tier vanilla cake with custom decorations',
            ],
            [
                'description' => 'Kissimmee Farmers Market - Saturday',
                'amount' => 398.25,
                'source' => 'farmers_market',
                'notes' => 'New location performing well, lots of repeat customers',
            ],
            [
                'description' => 'Corporate Catering - Johnson & Associates',
                'amount' => 450.00,
                'source' => 'catering',
                'notes' => 'Morning pastries and coffee for 30-person meeting',
            ],
            [
                'description' => 'Winter Haven Farmers Market - Holiday Special',
                'amount' => 567.80,
                'source' => 'farmers_market',
                'notes' => 'Holiday cookies and seasonal items were huge hit',
            ],
            [
                'description' => 'Birthday Party Catering - Davis Family',
                'amount' => 185.00,
                'source' => 'catering',
                'notes' => 'Cupcake tower and cookies for 8-year-old birthday',
            ],
            [
                'description' => 'Davenport Community Center - Event Catering',
                'amount' => 325.75,
                'source' => 'catering',
                'notes' => 'Assorted pastries for community fundraiser',
            ],
            [
                'description' => 'Direct Cash Sale - Anniversary Cake',
                'amount' => 195.00,
                'source' => 'cash_sale',
                'notes' => 'Red velvet layer cake for 25th anniversary',
            ],
            [
                'description' => 'Winter Haven Farmers Market - Regular Weekend',
                'amount' => 445.60,
                'source' => 'farmers_market',
                'notes' => 'Steady sales across all product categories',
            ],
            [
                'description' => 'Office Catering - Central Florida Realty',
                'amount' => 280.00,
                'source' => 'catering',
                'notes' => 'Weekly delivery of fresh muffins and pastries',
            ],
            [
                'description' => 'Clermont Farmers Market - Special Event',
                'amount' => 378.90,
                'source' => 'farmers_market',
                'notes' => 'Earth Day festival brought extra foot traffic',
            ],
            [
                'description' => 'Wedding Consultation and Tasting',
                'amount' => 125.00,
                'source' => 'cash_sale',
                'notes' => 'Initial consultation fee for June wedding',
            ],
            [
                'description' => 'Baby Shower Catering - Martinez Family',
                'amount' => 215.50,
                'source' => 'catering',
                'notes' => 'Themed cupcakes and petit fours for 25 guests',
            ],
            [
                'description' => 'Kissimmee Farmers Market - Valentine\'s Weekend',
                'amount' => 492.30,
                'source' => 'farmers_market',
                'notes' => 'Valentine-themed treats were extremely popular',
            ],
        ];

        foreach ($incomes as $index => $incomeData) {
            // Spread incomes over the last 60 days, with some concentration on weekends for farmers markets
            $daysAgo = rand(1, 60);
            $date = Carbon::now()->subDays($daysAgo);
            
            // If it's a farmers market income, try to put it on a weekend
            if ($incomeData['source'] === 'farmers_market') {
                // Adjust to nearest weekend day (Saturday or Sunday)
                $dayOfWeek = $date->dayOfWeek;
                if ($dayOfWeek < 6) { // Monday-Friday
                    $daysToSaturday = 6 - $dayOfWeek;
                    $daysToSunday = 7 - $dayOfWeek;
                    // Choose the closer weekend day
                    $adjustment = $daysToSaturday <= $daysToSunday ? $daysToSaturday : -($dayOfWeek);
                    $date->addDays($adjustment);
                }
            }

            Income::create([
                'description' => $incomeData['description'],
                'amount' => $incomeData['amount'],
                'source' => $incomeData['source'],
                'date' => $date,
                'notes' => $incomeData['notes'] ?? null,
            ]);
        }
    }
}