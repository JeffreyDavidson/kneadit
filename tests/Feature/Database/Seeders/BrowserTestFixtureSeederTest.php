<?php

use App\Enums\Customers\RfmSegment;
use App\Models\Inventory\Product;
use App\Reports\Customers\RfmReport;
use App\Services\Settings\TenantSettings;
use Database\Seeders\BrowserTestFixtureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('browser fixtures seed a champion customer for the RFM report', function () {
    resolve(BrowserTestFixtureSeeder::class)->run();
    resolve(BrowserTestFixtureSeeder::class)->run();

    $report = resolve(RfmReport::class)->generate();

    expect($report->total)->toBe(1)
        ->and($report->segments[RfmSegment::Champions->value]->count)->toBe(1)
        ->and($report->segments[RfmSegment::Champions->value]->sampleCustomers[0]->email)
        ->toBe('browser-test-rfm@kneadit.test');
});

test('browser fixtures seed delivery tiers, a free-delivery minimum and an orderable product', function () {
    resolve(BrowserTestFixtureSeeder::class)->run();
    resolve(BrowserTestFixtureSeeder::class)->run();

    $orders = resolve(TenantSettings::class)->orders;

    expect($orders->deliveryEnabled)->toBeTrue()
        ->and($orders->deliveryFeeTiers)->toHaveCount(2)
        ->and($orders->deliveryFee('1', 30.0))->toBe(12.0)
        ->and($orders->deliveryFee('1', BrowserTestFixtureSeeder::DELIVERY_FREE_MINIMUM))->toBe(0.0)
        ->and(Product::query()->where('name', BrowserTestFixtureSeeder::DELIVERY_PRODUCT_NAME)->count())->toBe(1);
});
