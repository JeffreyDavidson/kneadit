<?php

use App\Models\Financial\GiftCard;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('usable scope returns active non-expired cards with balance', function () {
    $usable = GiftCard::factory()->create();
    GiftCard::factory()->depleted()->create();
    GiftCard::factory()->expired()->create();

    $results = GiftCard::query()->usable()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($usable->id);
});

test('usable and expired scopes treat the expiry day as inclusive in the bakery timezone', function (string $now, bool $usable) {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow($now);
    GiftCard::factory()->create(['expires_at' => '2026-12-31']);

    expect(GiftCard::query()->usable()->count())->toBe($usable ? 1 : 0)
        ->and(GiftCard::query()->expired()->count())->toBe($usable ? 0 : 1);
})->with([
    'noon on the expiry day' => ['2026-12-31 17:00', true],
    '23:00 bakery time (already Jan 1 in UTC)' => ['2027-01-01 04:00', true],
    'midnight starting the next bakery day' => ['2027-01-01 05:00', false],
]);
