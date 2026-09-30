<?php

use App\Models\Inventory\SeasonalItem;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

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

test('current availability includes both date boundaries', function () {
    test()->travelTo(now()->setTime(12, 0));
    $today = now()->toDateString();
    $item = SeasonalItem::factory()->create([
        'available_from' => $today,
        'available_until' => $today,
    ]);

    expect($item->is_currently_available)->toBeTrue()
        ->and(SeasonalItem::query()->current()->whereKey($item)->exists())->toBeTrue();
});

test('scopes and availability use the bakery-local day', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');
    $endsToday = SeasonalItem::factory()->create(['available_from' => '2026-09-01', 'available_until' => '2026-10-05']);
    $startsTomorrow = SeasonalItem::factory()->create(['available_from' => '2026-10-06', 'available_until' => '2026-11-01']);
    $endedYesterday = SeasonalItem::factory()->create(['available_from' => '2026-09-01', 'available_until' => '2026-10-04']);

    $current = SeasonalItem::query()->current()->pluck('id')->all();
    $upcoming = SeasonalItem::query()->upcoming()->pluck('id')->all();
    $expired = SeasonalItem::query()->expired()->pluck('id')->all();

    expect($current)->toBe([$endsToday->id])
        ->and($upcoming)->toBe([$startsTomorrow->id])
        ->and($expired)->toBe([$endedYesterday->id])
        ->and($endsToday->is_currently_available)->toBeTrue()
        ->and($endedYesterday->is_currently_available)->toBeFalse();
});
