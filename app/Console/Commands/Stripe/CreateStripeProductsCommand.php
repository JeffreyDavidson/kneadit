<?php

namespace App\Console\Commands\Stripe;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Laravel\Cashier\Cashier;

#[Signature('stripe:create-products')]
#[Description('Create KneadIt subscription products and prices in Stripe')]
class CreateStripeProductsCommand extends Command
{
    public function handle(): int
    {
        $stripe = Cashier::stripe();
        $plans = config('kneadit.plans');

        foreach ($plans as $key => $plan) {
            $this->info("Creating product: {$plan['name']}...");

            $product = $stripe->products->create([
                'name' => "KneadIt {$plan['name']}",
                'description' => $plan['description'],
                'metadata' => ['plan_key' => $key],
            ]);

            $price = $stripe->prices->create([
                'product' => $product->id,
                'unit_amount' => $plan['founding_price_monthly'],
                'currency' => 'usd',
                'recurring' => ['interval' => 'month'],
                'metadata' => ['plan_key' => $key, 'rate' => 'founding'],
            ]);

            $this->info("  Product: {$product->id}");
            $this->info("  Price:   {$price->id}");
            $this->newLine();
        }

        $this->warn('Add these price IDs to your .env file:');
        $this->info('STRIPE_PRICE_STARTER=<starter price id>');
        $this->info('STRIPE_PRICE_GROWTH=<growth price id>');
        $this->info('STRIPE_PRICE_PRO=<pro price id>');

        return self::SUCCESS;
    }
}
