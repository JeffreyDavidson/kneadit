<?php

use App\Models\SeasonalItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('current scope returns items available now', function () {
    $current = SeasonalItem::factory()->create([
        'available_from' => now()->subWeek(),
        'available_until' => now()->addWeek(),
    ]);
    SeasonalItem::factory()->create([
        'available_from' => now()->addMonth(),
        'available_until' => now()->addMonths(2),
    ]);

    $results = SeasonalItem::query()->current()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($current->id);
});
