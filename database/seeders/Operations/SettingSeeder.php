<?php

namespace Database\Seeders\Operations;

use App\Models\Platform\Setting;
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
                'key' => 'hero_style',
                'value' => 'split',
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
        $settings[] = [
            'key' => 'order_journey_steps',
            'value' => json_encode(config('kneadit.default_journey_steps')),
        ];

        // Homepage sections configuration
        $settings[] = [
            'key' => 'homepage_sections',
            'value' => json_encode([
                'hero' => ['visible' => true, 'order' => 1],
                'about' => ['visible' => true, 'order' => 2],
                'featured_products' => ['visible' => true, 'order' => 3, 'count' => 6, 'title' => 'Our Favorites', 'subtitle' => 'Freshly made'],
                'categories' => ['visible' => true, 'order' => 4, 'title' => 'What We Bake', 'subtitle' => 'Something for everyone'],
                'reviews' => ['visible' => true, 'order' => 5, 'count' => 3, 'title' => 'Kind Words', 'subtitle' => 'What our customers say'],
                'gallery' => ['visible' => true, 'order' => 6, 'count' => 4, 'title' => 'Customer Gallery', 'subtitle' => 'Shared by our community'],
                'blog' => ['visible' => true, 'order' => 7, 'count' => 3, 'title' => 'Latest Updates', 'subtitle' => 'From our kitchen'],
                'cta' => ['visible' => true, 'order' => 8, 'heading' => 'Treat Yourself Today', 'button_text' => 'Start Your Order'],
                'social' => ['visible' => true, 'order' => 9],
            ]),
        ];

        // Page content — all storefront copy in one JSON blob
        $settings[] = [
            'key' => 'page_content',
            'value' => json_encode([
                'menu' => [
                    'hero_eyebrow' => '{{store_name}}',
                    'hero_title' => 'Our Menu',
                    'hero_subtitle' => "Everything we make, crafted with care. Browse at your pace — when something catches your eye, we'll have it freshly prepared just for you.",
                    'category_eyebrow' => 'Collection',
                    'cta_script' => 'Ready to order?',
                    'cta_heading' => "Let's get baking.",
                    'cta_description' => 'All orders need {{lead_time}} hours notice. Place yours now.',
                    'cta_button' => 'Place an Order',
                ],
                'contact' => [
                    'hero_eyebrow' => 'Get in Touch',
                    'hero_title' => "We'd Love to\nHear From You",
                    'hero_subtitle' => "Questions, special requests, or just want to say hello — we're all ears.",
                    'form_eyebrow' => 'Send a Message',
                    'hours_eyebrow' => 'Hours',
                    'faq_eyebrow' => 'FAQ',
                    'faq_heading' => 'Common Questions',
                ],
                'gallery' => [
                    'hero_eyebrow' => 'From Our Customers',
                    'hero_title' => 'Customer Gallery',
                    'hero_subtitle' => 'See what our customers are creating and enjoying!',
                    'empty_heading' => 'Your Photos Will Shine Here',
                    'empty_description' => "We'd love to see what you're baking and enjoying! Share a photo of your order and it'll appear right here for the whole community to see.",
                    'empty_script' => 'Be the first to share!',
                    'upload_eyebrow' => 'Share Yours',
                    'upload_heading' => 'Share Your Photo',
                    'upload_description' => 'Show off your order! Photos will appear after approval.',
                ],
                'reviews' => [
                    'hero_eyebrow' => 'What People Say',
                    'hero_title' => 'Kind Words',
                    'rating_eyebrow' => 'Rating Breakdown',
                    'all_reviews_label' => 'All Reviews',
                    'empty_heading' => 'No reviews yet',
                    'empty_description' => 'Be the first to share your experience.',
                    'cta_script' => 'Enjoyed something?',
                    'cta_heading' => "We'd love to hear about it.",
                    'cta_description' => 'Your feedback helps us bake better and helps others discover their next favorite treat.',
                    'cta_button' => 'Leave a Review',
                ],
                'about' => [
                    'hero_eyebrow' => 'The story behind',
                    'story_eyebrow' => 'Our Story',
                    'values_eyebrow' => 'Our Values',
                    'values_heading' => 'What We Believe',
                    'values' => [
                        ['title' => 'Quality Ingredients', 'description' => 'We source only the finest, freshest ingredients for every recipe. No shortcuts, no compromises — just honest baking.'],
                        ['title' => 'Freshly Baked', 'description' => 'Everything is baked fresh for your order. We believe in delivering the best experience, every single time.'],
                        ['title' => 'Handmade with Love', 'description' => 'Every product is handcrafted by skilled bakers who take pride in their craft and care about every detail.'],
                    ],
                    'location_eyebrow' => 'Find Us',
                    'social_eyebrow' => 'Follow Along',
                    'cta_script' => 'Come taste the difference',
                    'cta_heading' => 'Ready to place an order?',
                    'cta_button' => 'Order Now',
                ],
                'catering' => [
                    'hero_eyebrow' => 'Premium Catering',
                    'hero_title' => 'Events & Catering',
                    'hero_subtitle' => 'Let us make your celebration unforgettable',
                    'hero_button' => 'Request a Quote',
                    'occasions_eyebrow' => 'What We Offer',
                    'occasions_heading' => 'Perfect for Every Occasion',
                    'occasions' => [
                        ['title' => 'Weddings', 'description' => 'Custom wedding cakes, dessert tables, pastry towers, and sweet treats to make your big day even sweeter.'],
                        ['title' => 'Corporate Events', 'description' => 'Professional catering for meetings, launches, office parties, and team celebrations.'],
                        ['title' => 'Parties & Celebrations', 'description' => 'Birthday parties, holiday gatherings, baby showers — we bring the sweetness to any celebration.'],
                    ],
                    'process_eyebrow' => 'Simple Process',
                    'process_heading' => 'How It Works',
                    'process_steps' => [
                        ['title' => 'Tell Us About Your Event', 'description' => 'Fill out the inquiry form with your event details.'],
                        ['title' => 'Get a Custom Quote', 'description' => "We'll craft a personalized quote based on your needs."],
                        ['title' => 'Confirm Your Order', 'description' => 'Review and confirm — we handle the rest.'],
                        ['title' => 'Enjoy!', 'description' => 'Fresh, beautiful baked goods delivered for your event.'],
                    ],
                    'testimonial_script' => 'What our clients say',
                    'testimonial_quote' => "The dessert spread at our wedding was absolutely stunning. Every guest raved about the pastries and the cake was a masterpiece. We couldn't have asked for a better experience!",
                    'testimonial_attribution' => 'A Happy Couple',
                    'form_eyebrow' => 'Ready to get started?',
                    'form_heading' => 'Request a Quote',
                ],
                'gift_cards' => [
                    'hero_eyebrow' => 'A Sweet Gesture',
                    'hero_title' => "Give the Gift of\nFresh Baked Goods",
                    'hero_subtitle' => "A treat they'll remember long after the last crumb",
                    'preview_label' => 'Preview',
                    'amount_label' => 'Select Amount',
                    'balance_heading' => 'Check Gift Card Balance',
                    'details_eyebrow' => 'Details',
                    'details_heading' => 'Send Your Gift',
                    'recipient_label' => 'Recipient Info',
                    'success_heading' => 'Gift Card Purchased!',
                    'success_description' => 'Share the code below with the lucky recipient.',
                ],
                'loyalty' => [
                    'hero_eyebrow' => 'Rewards Program',
                    'hero_subtitle' => 'Earn points with every order, unlock delicious rewards',
                    'paused_message' => 'Our loyalty program is currently paused. Check back soon!',
                    'check_heading' => 'Check Your Points',
                    'rewards_eyebrow' => 'Unlock',
                    'rewards_heading' => 'Available Rewards',
                    'how_it_works_eyebrow' => 'Simple as',
                    'how_it_works_heading' => 'How It Works',
                    'how_it_works_steps' => [
                        ['title' => 'Place an Order', 'description' => 'Order your favorite baked goods as usual.'],
                        ['title' => 'Earn Points', 'description' => 'Get {{points_per_dollar}} points for every $1 spent when delivered.'],
                        ['title' => 'Redeem Rewards', 'description' => 'Use your points for discounts and free treats!'],
                    ],
                ],
                'order_confirmation' => [
                    'hero_eyebrow' => 'Order Placed',
                    'hero_title' => 'Thank You!',
                    'hero_description' => "Your order has been received and we'll start preparing your items right away.",
                    'details_heading' => 'Order Details',
                    'journey_eyebrow' => 'What Happens Next',
                    'journey_heading' => 'Your Order Journey',
                ],
                'submit_review' => [
                    'hero_eyebrow' => "We'd Love to Hear From You",
                    'hero_title' => 'How Was Your Order?',
                    'rating_label' => 'Your Rating',
                    'rating_descriptions' => ['', 'Could be better', 'It was okay', 'Pretty good!', 'Really great!', 'Absolutely amazing!'],
                    'comment_label' => 'Tell Us About Your Experience',
                    'comment_placeholder' => 'What did you love? What could we improve?',
                    'photo_label' => 'Add a Photo',
                    'submit_button' => 'Submit Review',
                    'success_title' => 'Thank You!',
                    'success_description' => 'Your review has been submitted and will appear once approved. We appreciate your feedback!',
                ],
                'survey' => [
                    'hero_eyebrow' => 'Your Opinion Matters',
                    'submit_button' => 'Submit Feedback',
                    'submit_footer' => 'Your feedback helps us bake better for you',
                    'success_title' => 'Thank You!',
                    'success_description' => 'Your feedback has been submitted. We appreciate you taking the time to share your thoughts!',
                ],
                'order_tracking' => [
                    'hero_eyebrow' => 'Order Status',
                    'hero_title' => 'Track Your Order',
                    'hero_subtitle' => 'Enter your email to see how your order is coming along.',
                    'email_label' => 'Email Address',
                    'lookup_button' => 'Look Up',
                    'empty_heading' => 'No orders found',
                    'empty_description_prefix' => "We couldn't find any orders for",
                    'empty_hint' => "Make sure you're using the same email you ordered with.",
                    'items_label' => 'Items Ordered',
                    'messages_label' => 'Messages',
                    'reorder_button' => 'Order Again',
                    'cta_script' => 'Ready to order?',
                    'cta_heading' => 'Start your first order today.',
                    'cta_button' => 'Order Now',
                ],
            ]),
        ];

        foreach ($settings as $setting) {
            Setting::query()->updateOrCreate(['key' => $setting['key']], ['value' => $setting['value']]);
        }
    }
}
