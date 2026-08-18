<?php

use App\Models\Inventory\SeasonalItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

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

test('isCurrentlyAvailable method agrees with current scope', function () {
    $current = SeasonalItem::factory()->create([
        'available_from' => now()->subWeek(),
        'available_until' => now()->addWeek(),
    ]);
    $future = SeasonalItem::factory()->create([
        'available_from' => now()->addMonth(),
        'available_until' => now()->addMonths(2),
    ]);
    $expired = SeasonalItem::factory()->create([
        'available_from' => now()->subMonths(2),
        'available_until' => now()->subMonth(),
    ]);

    $scopeIds = SeasonalItem::query()->current()->pluck('id')->sort()->values();
    $methodIds = SeasonalItem::all()
        ->filter(fn (SeasonalItem $item) => $item->is_currently_available)
        ->pluck('id')->sort()->values();

    expect($methodIds->all())->toBe($scopeIds->all());
});
