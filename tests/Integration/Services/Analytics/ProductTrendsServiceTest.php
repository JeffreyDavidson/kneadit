<?php

use App\Services\Analytics\ProductTrendsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('returns array of product trends for a month', function () {
    $service = new ProductTrendsService;
    $result = $service->calculate(2026, 3);

    expect($result)->toBeArray();
});
