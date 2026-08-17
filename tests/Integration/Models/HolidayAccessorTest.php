<?php

use App\Models\Operations\Holiday;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('date is cast to Carbon', function () {
    $holiday = Holiday::factory()->create(['date' => '2026-12-25']);

    expect($holiday->refresh()->date)->toBeInstanceOf(Carbon::class);
});

test('lead_days is cast to integer', function () {
    $holiday = Holiday::factory()->create(['lead_days' => 14]);

    expect($holiday->refresh()->lead_days)->toBeInt()->toBe(14);
});
