<?php

return [
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

    'trial_days' => 30,
];
