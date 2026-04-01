<?php

use App\Models\Operations\Holiday;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('is_upcoming returns true for future holidays', function () {
    $holiday = Holiday::factory()->create(['date' => now()->addDays(10)]);

    expect($holiday->is_upcoming)->toBeTrue();
});

test('is_upcoming returns false for past holidays', function () {
    $holiday = Holiday::factory()->create(['date' => now()->subDay()]);

    expect($holiday->is_upcoming)->toBeFalse();
});

test('days_away calculates correct number of days', function () {
    $holiday = Holiday::factory()->create(['date' => now()->addDays(5)->startOfDay()]);

    expect($holiday->days_away)->toBeGreaterThanOrEqual(4)
        ->and($holiday->days_away)->toBeLessThanOrEqual(5);
});
