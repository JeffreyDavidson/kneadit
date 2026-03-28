<?php

use App\Models\Income;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('forYear filters incomes by year', function () {
    Income::factory()->create(['date' => '2026-03-15']);
    Income::factory()->create(['date' => '2025-06-01']);

    $results = Income::query()->forYear(2026)->get();

    expect($results)->toHaveCount(1);
});
