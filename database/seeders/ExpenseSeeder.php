<?php

namespace Database\Seeders;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $expenses = [
            // Ingredients
            [
                'description' => 'Organic flour bulk purchase - King Arthur',
                'amount' => 125.50,
                'category' => 'ingredients',
                'business_percentage' => 100,
                'notes' => '50lb bags of bread and all-purpose flour',
            ],
            [
                'description' => 'Premium butter - European style',
                'amount' => 85.75,
                'category' => 'ingredients',
                'business_percentage' => 100,
                'notes' => 'For croissants and pastries',
            ],
            [
                'description' => 'Valrhona chocolate - dark and milk',
                'amount' => 156.00,
                'category' => 'ingredients',
                'business_percentage' => 100,
                'notes' => 'Premium chocolate for ganache and baking',
            ],
            [
                'description' => 'Fresh eggs from local farm',
                'amount' => 45.25,
                'category' => 'ingredients',
                'business_percentage' => 100,
                'notes' => '10 dozen farm-fresh eggs',
            ],
            [
                'description' => 'Seasonal berries - farmers market',
                'amount' => 67.50,
                'category' => 'ingredients',
                'business_percentage' => 100,
                'notes' => 'Fresh strawberries, blueberries, raspberries',
            ],
            [
                'description' => 'Vanilla extract and spices',
                'amount' => 78.30,
                'category' => 'ingredients',
                'business_percentage' => 100,
                'notes' => 'Pure vanilla, cinnamon, nutmeg, cardamom',
            ],
            [
                'description' => 'Cream cheese and dairy products',
                'amount' => 92.40,
                'category' => 'ingredients',
                'business_percentage' => 100,
                'notes' => 'For cheesecakes and frostings',
            ],

            // Packaging
            [
                'description' => 'Custom cake boxes - various sizes',
                'amount' => 145.60,
                'category' => 'packaging',
                'business_percentage' => 100,
                'notes' => 'Branded boxes for cakes and pastries',
            ],
            [
                'description' => 'Food-safe packaging supplies',
                'amount' => 73.25,
                'category' => 'packaging',
                'business_percentage' => 100,
                'notes' => 'Bags, containers, labels, tissue paper',
            ],
            [
                'description' => 'Eco-friendly takeout containers',
                'amount' => 89.15,
                'category' => 'packaging',
                'business_percentage' => 100,
                'notes' => 'Compostable containers for individual items',
            ],

            // Supplies
            [
                'description' => 'Kitchen equipment maintenance',
                'amount' => 235.00,
                'category' => 'supplies',
                'business_percentage' => 100,
                'notes' => 'Stand mixer repair and calibration',
            ],
            [
                'description' => 'Baking pans and molds',
                'amount' => 167.80,
                'category' => 'supplies',
                'business_percentage' => 100,
                'notes' => 'Professional-grade cake pans and tart molds',
            ],
            [
                'description' => 'Cleaning and sanitation supplies',
                'amount' => 54.75,
                'category' => 'supplies',
                'business_percentage' => 100,
                'notes' => 'Food-safe cleaners and sanitizers',
            ],
            [
                'description' => 'Parchment paper and baking sheets',
                'amount' => 42.30,
                'category' => 'supplies',
                'business_percentage' => 100,
                'notes' => 'Professional parchment and sheet pans',
            ],

            // Booth Fees
            [
                'description' => 'Winter Haven Farmers Market - Monthly fee',
                'amount' => 120.00,
                'category' => 'booth_fees',
                'business_percentage' => 100,
                'notes' => 'Premium booth location',
            ],
            [
                'description' => 'Clermont Farmers Market - Weekend fee',
                'amount' => 75.00,
                'category' => 'booth_fees',
                'business_percentage' => 100,
                'notes' => 'Saturday market booth rental',
            ],
            [
                'description' => 'Holiday craft fair - Kissimmee',
                'amount' => 85.00,
                'category' => 'booth_fees',
                'business_percentage' => 100,
                'notes' => 'Two-day holiday event booth',
            ],

            // Delivery
            [
                'description' => 'Vehicle maintenance - delivery van',
                'amount' => 187.50,
                'category' => 'delivery',
                'business_percentage' => 90,
                'notes' => 'Oil change and tire rotation',
            ],
            [
                'description' => 'Fuel for delivery routes',
                'amount' => 125.75,
                'category' => 'delivery',
                'business_percentage' => 85,
                'notes' => 'Weekly fuel for local deliveries',
            ],
            [
                'description' => 'Insulated delivery bags',
                'amount' => 95.40,
                'category' => 'delivery',
                'business_percentage' => 100,
                'notes' => 'Temperature-controlled transport bags',
            ],

            // Marketing
            [
                'description' => 'Social media advertising - Facebook/Instagram',
                'amount' => 150.00,
                'category' => 'marketing',
                'business_percentage' => 100,
                'notes' => 'Monthly social media ad spend',
            ],
            [
                'description' => 'Professional photography session',
                'amount' => 275.00,
                'category' => 'marketing',
                'business_percentage' => 100,
                'notes' => 'Product photos for website and social media',
            ],
            [
                'description' => 'Business cards and brochures',
                'amount' => 68.50,
                'category' => 'marketing',
                'business_percentage' => 100,
                'notes' => '500 business cards and 200 brochures',
            ],
            [
                'description' => 'Local magazine advertisement',
                'amount' => 195.00,
                'category' => 'marketing',
                'business_percentage' => 100,
                'notes' => 'Quarter-page ad in Central Florida Living',
            ],

            // Mixed business/personal expenses
            [
                'description' => 'Phone bill - business line',
                'amount' => 65.00,
                'category' => 'supplies',
                'business_percentage' => 75,
                'notes' => 'Business phone with some personal use',
            ],
            [
                'description' => 'Internet service upgrade',
                'amount' => 89.99,
                'category' => 'supplies',
                'business_percentage' => 60,
                'notes' => 'Higher speed for online orders and video calls',
            ],
            [
                'description' => 'Office supplies and stationery',
                'amount' => 43.25,
                'category' => 'supplies',
                'business_percentage' => 80,
                'notes' => 'Invoices, order forms, pens, folders',
            ],

            // Recent expenses
            [
                'description' => 'Holiday spices and extracts',
                'amount' => 92.75,
                'category' => 'ingredients',
                'business_percentage' => 100,
                'notes' => 'Pumpkin spice, peppermint, eggnog flavoring',
            ],
            [
                'description' => 'Seasonal packaging - Valentine\'s Day',
                'amount' => 58.40,
                'category' => 'packaging',
                'business_percentage' => 100,
                'notes' => 'Heart-themed boxes and ribbons',
            ],
            [
                'description' => 'Insurance payment - business liability',
                'amount' => 347.00,
                'category' => 'supplies',
                'business_percentage' => 100,
                'notes' => 'Quarterly business insurance payment',
            ],
            [
                'description' => 'Farmers market tent replacement',
                'amount' => 156.80,
                'category' => 'supplies',
                'business_percentage' => 100,
                'notes' => 'Heavy-duty tent for outdoor markets',
            ],
        ];

        foreach ($expenses as $index => $expenseData) {
            // Spread expenses over the last 90 days
            $daysAgo = rand(1, 90);
            $date = Carbon::now()->subDays($daysAgo);

            Expense::create([
                'description' => $expenseData['description'],
                'amount' => $expenseData['amount'],
                'category' => $expenseData['category'],
                'date' => $date,
                'notes' => $expenseData['notes'] ?? null,
                'business_percentage' => $expenseData['business_percentage'],
            ]);
        }
    }
}