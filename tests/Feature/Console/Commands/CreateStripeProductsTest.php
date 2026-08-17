<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Testing\PendingCommand;
use JMac\Testing\Double;
use JMac\Testing\Matching\Argument;
use Stripe\Price;
use Stripe\Product;
use Stripe\Service\PriceService;
use Stripe\Service\ProductService;
use Stripe\StripeClient;

class FakeStripeProductsClient extends StripeClient
{
    public function __construct(
        public ProductService $products,
        public PriceService $prices,
    ) {}
}

function bindStripeProductsClient(ProductService $products, PriceService $prices): void
{
    app()->bind(
        StripeClient::class,
        fn () => new FakeStripeProductsClient($products, $prices),
    );
}

function createStripeProductsCommand(): PendingCommand
{
    return pendingArtisan('stripe:create-products');
}

test('stripe create-products command is registered and has correct signature', function () {
    $command = new App\Console\Commands\Stripe\CreateStripeProductsCommand;

    expect($command->getName())->toBe('stripe:create-products')
        ->and($command->getDescription())->toContain('Stripe');
});

test('stripe create-products iterates all configured plans', function () {
    $plans = Config::array('kneadit.plans');

    expect($plans)->not->toBeEmpty()
        ->and($plans)->each->toHaveKeys(['name', 'description', 'founding_price_monthly']);
});

test('stripe create-products creates products and prices for each plan', function () {
    $plans = Config::array('kneadit.plans');

    $product = new Product('prod_test123');
    $price = new Price('price_test456');

    $productsService = Double::for(ProductService::class);
    $productsService->expects('create')
        ->times(count($plans))
        ->returns($product);

    $pricesService = Double::for(PriceService::class);
    $pricesService->expects('create')
        ->times(count($plans))
        ->returns($price);

    bindStripeProductsClient($productsService, $pricesService);

    createStripeProductsCommand()
        ->expectsOutputToContain('prod_test123')
        ->expectsOutputToContain('price_test456')
        ->expectsOutputToContain('STRIPE_PRICE_STARTER')
        ->assertSuccessful();
});

test('stripe create-products passes correct data to stripe product creation', function () {
    $plans = Config::array('kneadit.plans');

    $product = new Product('prod_test');
    $price = new Price('price_test');

    $productsService = Double::for(ProductService::class);
    $productsService->expects('create')
        ->with(Argument::satisfies(function (mixed $data): bool {
            if (! is_array($data)) {
                return false;
            }

            $name = data_get($data, 'name');

            return is_string($name)
                && str_starts_with($name, 'KneadIt ')
                && data_get($data, 'description') !== null
                && data_get($data, 'metadata.plan_key') !== null;
        }))
        ->times(count($plans))
        ->returns($product);

    $pricesService = Double::for(PriceService::class);
    $pricesService->expects('create')
        ->with(Argument::satisfies(function (mixed $data): bool {
            if (! is_array($data)) {
                return false;
            }

            return data_get($data, 'product') === 'prod_test'
                && data_get($data, 'currency') === 'usd'
                && data_get($data, 'recurring.interval') === 'month'
                && data_get($data, 'metadata.plan_key') !== null
                && data_get($data, 'metadata.rate') === 'founding';
        }))
        ->times(count($plans))
        ->returns($price);

    bindStripeProductsClient($productsService, $pricesService);

    createStripeProductsCommand()
        ->assertSuccessful();
});

test('stripe create-products outputs env variable instructions', function () {
    $plans = Config::array('kneadit.plans');

    $productsService = Double::for(ProductService::class);
    $productsService->expects('create')
        ->times(count($plans))
        ->returns(new Product('prod_test'));

    $pricesService = Double::for(PriceService::class);
    $pricesService->expects('create')
        ->times(count($plans))
        ->returns(new Price('price_test'));

    bindStripeProductsClient($productsService, $pricesService);

    createStripeProductsCommand()
        ->expectsOutputToContain('STRIPE_PRICE_STARTER')
        ->expectsOutputToContain('STRIPE_PRICE_GROWTH')
        ->expectsOutputToContain('STRIPE_PRICE_PRO')
        ->assertSuccessful();
});
