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
                        'description' => 'Local delivery (0-5 miles)',
                    ],
                    [
                        'min_distance' => 5,
                        'max_distance' => 10,
                        'fee' => 5.00,
                        'description' => 'Extended delivery (5-10 miles)',
                    ],
                    [
                        'min_distance' => 10,
                        'max_distance' => 15,
                        'fee' => 8.00,
                        'description' => 'Long distance delivery (10-15 miles)',
                    ],
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
                    'sunday' => ['open' => '08:00', 'close' => '17:00'],
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
                    'twitter' => 'https://twitter.com/kneaditbakery',
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
            [
                'key' => 'delivery_enabled',
                'value' => '1',
            ],
            [
                'key' => 'storefront_enabled',
                'value' => '1',
            ],
            [
                'key' => 'loyalty_enabled',
                'value' => '1',
            ],
            [
                'key' => 'loyalty_points_per_dollar',
                'value' => '10',
            ],
            [
                'key' => 'loyalty_program_name',
                'value' => 'Rewards',
            ],
            [
                'key' => 'faq_items',
                'value' => json_encode([
                    [
                        'question' => 'How far in advance should I place my order?',
                        'answer' => 'We require a minimum of 24 hours notice for all orders to ensure quality and availability. Custom cakes require at least 7 days advance notice.',
                    ],
                    [
                        'question' => 'Do you offer delivery?',
                        'answer' => 'Yes! We deliver within 15 miles of our bakery. Delivery fees vary by distance, and orders over $50.00 qualify for free local delivery.',
                    ],
                    [
                        'question' => 'What payment methods do you accept?',
                        'answer' => 'We accept cash, PayPal, Venmo, and Zelle. Payment is collected at pickup or upon delivery.',
                    ],
                    [
                        'question' => 'Do you accommodate dietary restrictions or allergies?',
                        'answer' => 'We offer options for various dietary needs. Please note that our kitchen uses wheat, eggs, dairy, nuts, and soy. Mention any allergies in your order notes or contact us directly.',
                    ],
                ]),
            ],
        ];

        // Pricing engine settings
        $settings[] = ['key' => 'hourly_labor_rate', 'value' => '15'];
        $settings[] = ['key' => 'overhead_percentage', 'value' => '20'];
        $settings[] = ['key' => 'target_profit_margin', 'value' => '50'];

        // Dashboard widget configuration
        $settings[] = [
            'key' => 'dashboard_widgets',
            'value' => json_encode([
                'welcome_banner' => ['visible' => true, 'order' => 1],
                'stats_overview' => ['visible' => true, 'order' => 2],
                'revenue_chart' => ['visible' => true, 'order' => 3],
                'order_funnel' => ['visible' => true, 'order' => 4],
                'recent_orders' => ['visible' => true, 'order' => 5],
                'upcoming_orders' => ['visible' => true, 'order' => 6],
                'top_products' => ['visible' => true, 'order' => 7],
                'customer_insights' => ['visible' => true, 'order' => 8],
                'quick_actions' => ['visible' => true, 'order' => 9],
                'at_risk_customers' => ['visible' => true, 'order' => 10],
                'low_stock' => ['visible' => true, 'order' => 11],
                'storefront_views' => ['visible' => true, 'order' => 12],
                'birthday' => ['visible' => true, 'order' => 13],
                'recent_activity' => ['visible' => true, 'order' => 14],
            ]),
        ];

        // Announcement banner settings
        $settings[] = ['key' => 'announcement_text', 'value' => ''];
        $settings[] = ['key' => 'announcement_enabled', 'value' => '0'];
        $settings[] = ['key' => 'announcement_type', 'value' => 'info'];
        $settings[] = ['key' => 'default_shelf_life_days', 'value' => '3'];
        $settings[] = ['key' => 'birthday_program_enabled', 'value' => '1'];
        $settings[] = ['key' => 'birthday_discount_percentage', 'value' => '15'];
        $settings[] = ['key' => 'birthday_coupon_valid_days', 'value' => '7'];
        $settings[] = ['key' => 'weekly_digest_enabled', 'value' => '1'];
        $settings[] = ['key' => 'review_requests_enabled', 'value' => '1'];
        $settings[] = ['key' => 'review_request_delay_hours', 'value' => '24'];
        $settings[] = ['key' => 'catering_enabled', 'value' => '0'];
        $settings[] = ['key' => 'catering_minimum_guests', 'value' => '10'];
        $settings[] = ['key' => 'catering_lead_time_days', 'value' => '14'];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
