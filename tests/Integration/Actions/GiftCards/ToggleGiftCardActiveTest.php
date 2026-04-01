<?php

use App\Actions\GiftCards\ToggleGiftCardActive;
use App\Models\GiftCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it deactivates an active gift card', function () {
    $giftCard = GiftCard::factory()->create(['is_active' => true]);

    resolve(ToggleGiftCardActive::class)($giftCard);

    expect($giftCard->fresh()->is_active)->toBeFalse();
});

test('it activates an inactive gift card', function () {
    $giftCard = GiftCard::factory()->create(['is_active' => false]);

    resolve(ToggleGiftCardActive::class)($giftCard);

    expect($giftCard->fresh()->is_active)->toBeTrue();
});
