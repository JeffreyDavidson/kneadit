<?php

use App\Console\Commands\Stripe\CreateStripeProductsCommand;

test('stripe create-products command is registered and has correct signature', function () {
    $command = new CreateStripeProductsCommand;

    expect($command->getName())->toBe('stripe:create-products')
        ->and($command->getDescription())->toContain('Stripe');
});

test('stripe create-products iterates all configured plans', function () {
    $plans = config('kneadit.plans');

    expect($plans)->not->toBeEmpty()
        ->and($plans)->each->toHaveKeys(['name', 'description', 'founding_price_monthly']);
});
