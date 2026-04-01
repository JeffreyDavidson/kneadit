<?php

use App\Actions\GiftCards\RedeemGiftCard;
use App\Models\Financial\GiftCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('redeems gift card and decrements balance', function () {
    $card = GiftCard::factory()->create([
        'current_balance' => 100.00,
        'is_active' => true,
    ]);

    $result = resolve(RedeemGiftCard::class)($card->code, 30.00);

    expect($result->success)->toBeTrue()
        ->and($result->amountApplied)->toEqual(30.0)
        ->and($card->refresh()->current_balance)->toEqual(70.0);
});

test('redemption caps at available balance', function () {
    $card = GiftCard::factory()->create([
        'current_balance' => 20.00,
        'is_active' => true,
    ]);

    $result = resolve(RedeemGiftCard::class)($card->code, 50.00);

    expect($result->success)->toBeTrue()
        ->and($result->amountApplied)->toEqual(20.0)
        ->and($card->refresh()->current_balance)->toEqual(0.0);
});
