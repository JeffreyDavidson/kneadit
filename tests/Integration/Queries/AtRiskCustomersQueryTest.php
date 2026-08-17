<?php

use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Queries\Customers\AtRiskCustomersQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('identifies customers with no recent orders', function () {
    $atRisk = Customer::factory()->create();
    Order::factory()->recycle($atRisk)->create(['created_at' => now()->subDays(60)]);

    $active = Customer::factory()->create();
    Order::factory()->recycle($active)->create(['created_at' => now()->subDays(5)]);

    $result = AtRiskCustomersQuery::get(days: 30);

    expect($result)->toHaveCount(1)
        ->and($result->firstOrFail()->id)->toBe($atRisk->id);
});

test('count returns number of at-risk customers', function () {
    $atRisk = Customer::factory()->create();
    Order::factory()->recycle($atRisk)->create(['created_at' => now()->subDays(60)]);

    expect(AtRiskCustomersQuery::count(days: 30))->toBe(1);
});
