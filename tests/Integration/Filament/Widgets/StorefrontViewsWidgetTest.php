<?php

use App\Filament\Widgets\StorefrontViewsWidget;
use App\Models\Engagement\PageView;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Cache::flush();
});

test('counts views from the bakery-local day as today', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');
    // 16:00 and 20:30 on 2026-10-05 in New York; the next view is 23:00 on 2026-10-04.
    PageView::factory()->create(['product_id' => null, 'created_at' => '2026-10-05 20:00']);
    PageView::factory()->create(['product_id' => null, 'created_at' => '2026-10-06 00:30']);
    PageView::factory()->create(['product_id' => null, 'created_at' => '2026-10-05 03:00']);

    $card = (new StorefrontViewsWidget)->getCardData();

    expect($card['today'])->toBe(2);
});

test('counts views from the UTC day as today when no bakery timezone is set', function () {
    Date::setTestNow('2026-10-06 01:00');
    PageView::factory()->create(['product_id' => null, 'created_at' => '2026-10-06 00:30']);
    PageView::factory()->create(['product_id' => null, 'created_at' => '2026-10-05 20:00']);

    $card = (new StorefrontViewsWidget)->getCardData();

    expect($card['today'])->toBe(1);
});
