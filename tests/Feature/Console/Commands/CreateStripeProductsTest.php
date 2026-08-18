<?php

use Stripe\StripeClient;

test('stripe create-products command is registered and has correct signature', function () {
    $command = new App\Console\Commands\Stripe\CreateStripeProductsCommand;

    expect($command->getName())->toBe('stripe:create-products')
        ->and($command->getDescription())->toContain('Stripe');
});

test('stripe create-products iterates all configured plans', function () {
    $plans = config('kneadit.plans');

    expect($plans)->not->toBeEmpty()
        ->and($plans)->each->toHaveKeys(['name', 'description', 'founding_price_monthly']);
});

test('stripe create-products creates products and prices for each plan', function () {
    $plans = config('kneadit.plans');

    $productMock = new stdClass;
    $productMock->id = 'prod_test123';

    $priceMock = new stdClass;
    $priceMock->id = 'price_test456';

    $productsService = Mockery::mock();
    $productsService->shouldReceive('create')
        ->times(count($plans))
        ->andReturn($productMock);

    $pricesService = Mockery::mock();
    $pricesService->shouldReceive('create')
        ->times(count($plans))
        ->andReturn($priceMock);

    $stripeClient = Mockery::mock(StripeClient::class);
    $stripeClient->products = $productsService;
    $stripeClient->prices = $pricesService;

    app()->bind(StripeClient::class, fn () => $stripeClient);

    $this->artisan('stripe:create-products')
        ->expectsOutputToContain('prod_test123')
        ->expectsOutputToContain('price_test456')
        ->expectsOutputToContain('STRIPE_PRICE_STARTER')
        ->assertSuccessful();
});

test('stripe create-products passes correct data to stripe product creation', function () {
    $plans = config('kneadit.plans');

    $productMock = new stdClass;
    $productMock->id = 'prod_test';

    $priceMock = new stdClass;
    $priceMock->id = 'price_test';

    $productsService = Mockery::mock();
    $productsService->shouldReceive('create')
        ->withArgs(function (array $data) {
            return str_starts_with($data['name'], 'KneadIt ')
                && isset($data['description'])
                && isset($data['metadata']['plan_key']);
        })
        ->times(count($plans))
        ->andReturn($productMock);

    $pricesService = Mockery::mock();
    $pricesService->shouldReceive('create')
        ->withArgs(function (array $data) {
            return $data['product'] === 'prod_test'
                && $data['currency'] === 'usd'
                && $data['recurring']['interval'] === 'month'
                && isset($data['metadata']['plan_key'])
                && $data['metadata']['rate'] === 'founding';
        })
        ->times(count($plans))
        ->andReturn($priceMock);

    $stripeClient = Mockery::mock(StripeClient::class);
    $stripeClient->products = $productsService;
    $stripeClient->prices = $pricesService;

    app()->bind(StripeClient::class, fn () => $stripeClient);

    $this->artisan('stripe:create-products')
        ->assertSuccessful();
});

test('stripe create-products outputs env variable instructions', function () {
    $productMock = new stdClass;
    $productMock->id = 'prod_test';

    $priceMock = new stdClass;
    $priceMock->id = 'price_test';

    $productsService = Mockery::mock();
    $productsService->shouldReceive('create')->andReturn($productMock);

    $pricesService = Mockery::mock();
    $pricesService->shouldReceive('create')->andReturn($priceMock);

    $stripeClient = Mockery::mock(StripeClient::class);
    $stripeClient->products = $productsService;
    $stripeClient->prices = $pricesService;

    app()->bind(StripeClient::class, fn () => $stripeClient);

    $this->artisan('stripe:create-products')
        ->expectsOutputToContain('STRIPE_PRICE_STARTER')
        ->expectsOutputToContain('STRIPE_PRICE_GROWTH')
        ->expectsOutputToContain('STRIPE_PRICE_PRO')
        ->assertSuccessful();
});
