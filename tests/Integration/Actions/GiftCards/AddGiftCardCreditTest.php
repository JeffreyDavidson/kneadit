<?php

use App\Actions\GiftCards\AddGiftCardCredit;
use App\Models\Financial\GiftCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('adds credit to gift card balance', function () {
    $card = GiftCard::factory()->create(['current_balance' => 25.00]);

    $result = resolve(AddGiftCardCredit::class)($card, 10.00, 'Bonus credit');

    expect($result->current_balance->dollars())->toEqual(35.0)->and($card->transactions)->toHaveCount(1);
});
