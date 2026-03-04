<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'store_name',
                'value' => 'KneadIt Demo Bakery',
            ],
            [
                'key' => 'store_email',
                'value' => 'orders@kneaditbakery.com',
            ],
            [
                'key' => 'store_phone',
                'value' => '(863) 555-BAKE',
            ],
            [
                'key' => 'store_address',
                'value' => '123 Main Street, Davenport, FL 33837',
            ],
            [
                'key' => 'default_daily_capacity',
                'value' => '15',
            ],
            [
                'key' => 'delivery_fee_tiers',
                'value' => json_encode([
                    [
                        'min_distance' => 0,
                        'max_distance' => 5,
                        'fee' => 3.00,
                        'description' => 'Local delivery (0-5 miles)'
                    ],
                    [
                        'min_distance' => 5,
                        'max_distance' => 10,
                        'fee' => 5.00,
                        'description' => 'Extended delivery (5-10 miles)'
                    ],
                    [
                        'min_distance' => 10,
                        'max_distance' => 15,
                        'fee' => 8.00,
                        'description' => 'Long distance delivery (10-15 miles)'
                    ]
                ]),
            ],
            [
                'key' => 'operating_hours',
                'value' => json_encode([
                    'monday' => ['open' => '07:00', 'close' => '18:00'],
                    'tuesday' => ['open' => '07:00', 'close' => '18:00'],
                    'wednesday' => ['open' => '07:00', 'close' => '18:00'],
                    'thursday' => ['open' => '07:00', 'close' => '18:00'],
                    'friday' => ['open' => '07:00', 'close' => '19:00'],
                    'saturday' => ['open' => '06:00', 'close' => '19:00'],
                    'sunday' => ['open' => '08:00', 'close' => '17:00']
                ]),
            ],
            [
                'key' => 'allergy_disclaimer',
                'value' => 'Please be aware that our bakery uses wheat, eggs, dairy, nuts, and soy in our kitchen. While we take precautions to prevent cross-contamination, we cannot guarantee that any item is completely free from allergens. Please inform us of any allergies when placing your order.',
            ],
            [
                'key' => 'order_lead_time_hours',
                'value' => '24',
            ],
            [
                'key' => 'custom_cake_lead_time_days',
                'value' => '7',
            ],
            [
                'key' => 'max_delivery_distance_miles',
                'value' => '15',
            ],
            [
                'key' => 'free_delivery_minimum',
                'value' => '50.00',
            ],
            [
                'key' => 'tax_rate_percentage',
                'value' => '7.5',
            ],
            [
                'key' => 'payment_methods_accepted',
                'value' => json_encode(['cash', 'paypal', 'venmo', 'zelle']),
            ],
            [
                'key' => 'social_media_links',
                'value' => json_encode([
                    'facebook' => 'https://facebook.com/kneaditbakery',
                    'instagram' => 'https://instagram.com/kneaditbakery',
                    'twitter' => 'https://twitter.com/kneaditbakery'
                ]),
            ],
            [
                'key' => 'business_tagline',
                'value' => 'Where passion meets pastry - handcrafted baked goods made with love',
            ],
            [
                'key' => 'about_us_text',
                'value' => 'KneadIt Bakery is a family-owned artisan bakery located in the heart of Central Florida. We specialize in handcrafted breads, pastries, cakes, and seasonal specialties using traditional techniques and the finest local ingredients. From our signature sourdough to custom wedding cakes, every item is made fresh daily with passion and attention to detail.',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}