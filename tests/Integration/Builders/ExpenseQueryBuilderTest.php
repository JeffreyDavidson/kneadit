<?php

use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('forYear filters expenses by year', function () {
    Expense::factory()->create(['date' => '2026-03-15']);
    Expense::factory()->create(['date' => '2025-06-01']);

    $results = Expense::query()->forYear(2026)->get();

    expect($results)->toHaveCount(1);
});

test('forMonth filters expenses by year and month', function () {
    Expense::factory()->create(['date' => '2026-03-15']);
    Expense::factory()->create(['date' => '2026-04-01']);

    $results = Expense::query()->forMonth(2026, 3)->get();

    expect($results)->toHaveCount(1);
});
