<?php

use App\Enums\Orders\OrderStatus;
use App\Models\Orders\Order;
use App\Queries\Orders\OrderFunnelQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('returns every trackable stage with aggregated counts and totals', function () {
    Order::factory()->pending()->count(2)->create(['total' => 1500]);
    Order::factory()->confirmed()->create(['total' => 2500]);

    $stages = resolve(OrderFunnelQuery::class)->get();
    $pending = collect($stages)->firstWhere('key', OrderStatus::Pending->value);

    expect($stages)->toHaveCount(count(OrderStatus::trackableStatuses()))
        ->and($pending)->toMatchArray(['count' => 2, 'total_formatted' => '$30.00']);
});
