<?php

namespace Database\Seeders\Financial;

use App\Enums\Financial\IncomeSource;
use App\Models\Financial\Income;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;

class IncomeSeeder extends Seeder
{
    public function run(): void
    {
        if (Income::query()->count() > 0) {
            return;
        }

        $incomes = [
            [
                'description' => 'Winter Haven Farmers Market - Weekend Sales',
                'amount' => 485.75,
                'source' => IncomeSource::FarmersMarket,
                'notes' => 'Strong sales - sold out of sourdough and croissants',
            ],
            [
                'description' => 'Clermont Farmers Market - Saturday Morning',
                'amount' => 312.50,
                'source' => IncomeSource::FarmersMarket,
                'notes' => 'Good crowd, popular items were muffins and cookies',
            ],
            [
                'description' => 'Direct Cash Sale - Wedding Cake',
                'amount' => 275.00,
                'source' => IncomeSource::CashSale,
                'notes' => 'Three-tier vanilla cake with custom decorations',
            ],
            [
                'description' => 'Kissimmee Farmers Market - Saturday',
                'amount' => 398.25,
                'source' => IncomeSource::FarmersMarket,
                'notes' => 'New location performing well, lots of repeat customers',
            ],
            [
                'description' => 'Corporate Catering - Johnson & Associates',
                'amount' => 450.00,
                'source' => IncomeSource::Catering,
                'notes' => 'Morning pastries and coffee for 30-person meeting',
            ],
            [
                'description' => 'Winter Haven Farmers Market - Holiday Special',
                'amount' => 567.80,
                'source' => IncomeSource::FarmersMarket,
                'notes' => 'Holiday cookies and seasonal items were huge hit',
            ],
            [
                'description' => 'Birthday Party Catering - Davis Family',
                'amount' => 185.00,
                'source' => IncomeSource::Catering,
                'notes' => 'Cupcake tower and cookies for 8-year-old birthday',
            ],
            [
                'description' => 'Davenport Community Center - Event Catering',
                'amount' => 325.75,
                'source' => IncomeSource::Catering,
                'notes' => 'Assorted pastries for community fundraiser',
            ],
            [
                'description' => 'Direct Cash Sale - Anniversary Cake',
                'amount' => 195.00,
                'source' => IncomeSource::CashSale,
                'notes' => 'Red velvet layer cake for 25th anniversary',
            ],
            [
                'description' => 'Winter Haven Farmers Market - Regular Weekend',
                'amount' => 445.60,
                'source' => IncomeSource::FarmersMarket,
                'notes' => 'Steady sales across all product categories',
            ],
            [
                'description' => 'Office Catering - Central Florida Realty',
                'amount' => 280.00,
                'source' => IncomeSource::Catering,
                'notes' => 'Weekly delivery of fresh muffins and pastries',
            ],
            [
                'description' => 'Clermont Farmers Market - Special Event',
                'amount' => 378.90,
                'source' => IncomeSource::FarmersMarket,
                'notes' => 'Earth Day festival brought extra foot traffic',
            ],
            [
                'description' => 'Wedding Consultation and Tasting',
                'amount' => 125.00,
                'source' => IncomeSource::CashSale,
                'notes' => 'Initial consultation fee for June wedding',
            ],
            [
                'description' => 'Baby Shower Catering - Martinez Family',
                'amount' => 215.50,
                'source' => IncomeSource::Catering,
                'notes' => 'Themed cupcakes and petit fours for 25 guests',
            ],
            [
                'description' => 'Kissimmee Farmers Market - Valentine\'s Weekend',
                'amount' => 492.30,
                'source' => IncomeSource::FarmersMarket,
                'notes' => 'Valentine-themed treats were extremely popular',
            ],
        ];

        foreach ($incomes as $index => $incomeData) {
            // Spread incomes over the last 60 days, with some concentration on weekends for farmers markets
            $daysAgo = random_int(1, 60);
            $date = Date::now()->subDays($daysAgo);

            // If it's a farmers market income, try to put it on a weekend
            if ($incomeData['source'] === IncomeSource::FarmersMarket) {
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

            Income::query()->create([
                'description' => $incomeData['description'],
                'amount' => $incomeData['amount'],
                'source' => $incomeData['source'],
                'date' => $date,
                'notes' => $incomeData['notes'] ?? null,
            ]);
        }
    }
}
