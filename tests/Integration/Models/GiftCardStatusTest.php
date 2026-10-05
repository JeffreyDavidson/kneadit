<?php

use App\Enums\Financial\GiftCardStatus;
use App\Models\Financial\GiftCard;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('active gift card has Active status', function () {
    $card = GiftCard::factory()->create();

    expect(GiftCardStatus::resolve($card))->toBe(GiftCardStatus::Active);
});

test('depleted gift card has Depleted status', function () {
    $card = GiftCard::factory()->depleted()->create();

    expect(GiftCardStatus::resolve($card))->toBe(GiftCardStatus::Depleted);
});

test('expired gift card has Expired status', function () {
    $card = GiftCard::factory()->expired()->create();

    expect(GiftCardStatus::resolve($card))->toBe(GiftCardStatus::Expired);
});

test('inactive gift card has Inactive status', function () {
    $card = GiftCard::factory()->inactive()->create();

    expect(GiftCardStatus::resolve($card))->toBe(GiftCardStatus::Inactive);
});

test('isUsable returns true for active card with balance', function () {
    $card = GiftCard::factory()->create();

    expect($card->is_usable)->toBeTrue();
});

test('isUsable returns false for depleted card', function () {
    $card = GiftCard::factory()->depleted()->create();

    expect($card->is_usable)->toBeFalse();
});

test('a card expiring today in the bakery timezone is usable until the bakery day ends', function (string $now, GiftCardStatus $status) {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow($now);
    $card = GiftCard::factory()->create(['expires_at' => '2026-12-31']);

    expect(GiftCardStatus::resolve($card))->toBe($status);
})->with([
    'noon on the expiry day' => ['2026-12-31 17:00', GiftCardStatus::Active],
    '23:00 bakery time (already Jan 1 in UTC)' => ['2027-01-01 04:00', GiftCardStatus::Active],
    'midnight starting the next bakery day' => ['2027-01-01 05:00', GiftCardStatus::Expired],
]);
