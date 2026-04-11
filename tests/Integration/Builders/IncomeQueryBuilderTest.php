<?php

use App\Models\Financial\Income;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('forYear filters incomes by year', function () {
    Income::factory()->create(['date' => '2026-03-15']);
    Income::factory()->create(['date' => '2025-06-01']);

    $results = Income::query()->forYear(2026)->get();

    expect($results)->toHaveCount(1);
});

test('forMonth filters incomes by year and month', function () {
    Income::factory()->create(['date' => '2026-03-15']);
    Income::factory()->create(['date' => '2026-04-01']);
    Income::factory()->create(['date' => '2025-03-15']);

    $results = Income::query()->forMonth(2026, 3)->get();

    expect($results)->toHaveCount(1);
});
