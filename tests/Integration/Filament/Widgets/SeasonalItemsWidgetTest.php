<?php

use App\Filament\Widgets\SeasonalItemsWidget;
use App\Models\Inventory\SeasonalItem;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Cache::flush();
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');
});

test('counts items still in season on their last bakery-local day', function () {
    SeasonalItem::factory()->create(['available_from' => '2026-09-01', 'available_until' => '2026-10-05']);
    SeasonalItem::factory()->create(['available_from' => '2026-09-01', 'available_until' => '2026-10-04']);

    $count = (new SeasonalItemsWidget)->getCurrentlyInSeasonCount();

    expect($count)->toBe(1);
});

test('lists items coming soon from the bakery-local tomorrow', function () {
    SeasonalItem::factory()->create(['available_from' => '2026-10-06', 'available_until' => '2026-11-06']);
    SeasonalItem::factory()->create(['available_from' => '2026-10-05', 'available_until' => '2026-11-06']);

    $comingSoon = (new SeasonalItemsWidget)->getComingSoon();

    expect($comingSoon)->toHaveCount(1)
        ->and($comingSoon[0]['date'])->toBe('Oct 6');
});

test('lists items ending soon through the bakery-local today', function () {
    SeasonalItem::factory()->create(['available_from' => '2026-09-01', 'available_until' => '2026-10-05']);
    SeasonalItem::factory()->create(['available_from' => '2026-09-01', 'available_until' => '2026-10-04']);

    $endingSoon = (new SeasonalItemsWidget)->getEndingSoon();

    expect($endingSoon)->toHaveCount(1)
        ->and($endingSoon[0]['date'])->toBe('Oct 5');
});
