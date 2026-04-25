<?php

return [
    /*
    |--------------------------------------------------------------------------
    | App version
    |--------------------------------------------------------------------------
    |
    | Read from the VERSION file at the project root. Deploy scripts and the
    | release process update this file with `git describe --tags --abbrev=0`.
    |
    */

    'version' => is_file(base_path('VERSION'))
        ? trim((string) file_get_contents(base_path('VERSION')))
        : 'dev',

    /*
    |--------------------------------------------------------------------------
    | KneadIt SaaS Plans
    |--------------------------------------------------------------------------
    |
    | Define subscription tiers. Stripe Price IDs are set after creating
    | products via the artisan command or Stripe dashboard.
    |
    */

    'plans' => [
        'starter' => [
            'name' => 'Starter',
            'description' => 'Take orders & sell',
            'price_monthly' => 900, // cents
            'founding_price_monthly' => 900, // cents (founding rate)
            'regular_price_monthly' => 1500, // cents
            'features' => [
                'Orders & order management',
                'Storefront website',
                'Customer directory',
                'Basic dashboard',
                'Email notifications',
            ],
            'limits' => [
                'products' => 25,
                'orders_per_month' => 100,
            ],
        ],
        'growth' => [
            'name' => 'Growth',
            'description' => 'Manage the business',
            'price_monthly' => 1900,
            'founding_price_monthly' => 1900,
            'regular_price_monthly' => 2900,
            'features' => [
                'Everything in Starter',
                'PayPal invoicing',
                'Financial dashboard & reports',
                'Recipe & cost management',
                'Coupons & discounts',
                'Customer notes & favorites',
            ],
            'limits' => [
                'products' => 100,
                'orders_per_month' => 500,
            ],
        ],
        'pro' => [
            'name' => 'Pro',
            'description' => 'Optimize & scale',
            'price_monthly' => 2900,
            'founding_price_monthly' => 2900,
            'regular_price_monthly' => 4500,
            'features' => [
                'Everything in Growth',
                'Review analytics & trends',
                'Instagram caption generator',
                'Holiday planning calendar',
                'Delivery route planning',
                'Custom branding & colors',
                'Priority support',
            ],
            'limits' => [
                'products' => null, // unlimited
                'orders_per_month' => null,
            ],
        ],
    ],

    'trial_days' => (int) env('TRIAL_DAYS', 30),

    'invitation_expiry_days' => (int) env('INVITATION_EXPIRY_DAYS', 7),

    'stripe_prices' => [
        'starter' => env('STRIPE_PRICE_STARTER'),
        'growth' => env('STRIPE_PRICE_GROWTH'),
        'pro' => env('STRIPE_PRICE_PRO'),
    ],

    'delivery_fees' => [
        'under5' => 0,
        '5to10' => 5.00,
        '10to15' => 10.00,
        'over15' => 15.00,
    ],

    'stripe_connect' => [
        'client_id' => env('STRIPE_CONNECT_CLIENT_ID'),
        'webhook_secret' => env('STRIPE_CONNECT_WEBHOOK_SECRET'),
    ],

    'default_journey_steps' => [
        [
            'title' => 'Confirmation',
            'description' => 'You\'ll receive an email confirmation with your order details shortly.',
        ],
        [
            'title' => 'Preparation',
            'description' => 'Our bakers will craft your items fresh on your scheduled date.',
        ],
        [
            'title' => 'Delivery',
            'description_delivery' => 'We\'ll deliver your fresh items right to your door.',
            'description_pickup' => 'Your items will be warm and ready for you to pick up.',
        ],
    ],

    'default_catering_occasions' => [
        ['title' => 'Weddings', 'description' => 'Custom wedding cakes, dessert tables, pastry towers, and sweet treats to make your big day even sweeter.', 'icon' => 'heart'],
        ['title' => 'Corporate Events', 'description' => 'Professional catering for meetings, launches, office parties, and team celebrations.', 'icon' => 'building-office'],
        ['title' => 'Parties & Celebrations', 'description' => 'Birthday parties, holiday gatherings, baby showers — we bring the sweetness to any celebration.', 'icon' => 'star'],
    ],

    'default_catering_process_steps' => [
        ['title' => 'Tell Us About Your Event', 'description' => 'Fill out the inquiry form with your event details.'],
        ['title' => 'Get a Custom Quote', 'description' => 'We\'ll craft a personalized quote based on your needs.'],
        ['title' => 'Confirm Your Order', 'description' => 'Review and confirm — we handle the rest.'],
        ['title' => 'Enjoy!', 'description' => 'Fresh, beautiful baked goods delivered for your event.'],
    ],

    'default_loyalty_steps' => [
        ['title' => 'Place an Order', 'description' => 'Order your favorite baked goods as usual.', 'icon' => 'shopping-cart'],
        ['title' => 'Earn Points', 'description' => 'Earn points for every $1 spent when delivered.', 'icon' => 'star'],
        ['title' => 'Redeem Rewards', 'description' => 'Use your points for discounts and free treats!', 'icon' => 'gift'],
    ],

    'default_rating_descriptions' => ['', 'Could be better', 'It was okay', 'Pretty good!', 'Really great!', 'Absolutely amazing!'],
];
