<?php

use App\Actions\GiftCards\ToggleGiftCardActive;
use App\Models\Financial\GiftCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it deactivates an active gift card', function () {
    $giftCard = GiftCard::factory()->active()->create();

    resolve(ToggleGiftCardActive::class)($giftCard);

    expect($giftCard->fresh()->is_active)->toBeFalse();
});

test('it activates an inactive gift card', function () {
    $giftCard = GiftCard::factory()->inactive()->create();

    resolve(ToggleGiftCardActive::class)($giftCard);

    expect($giftCard->fresh()->is_active)->toBeTrue();
});
