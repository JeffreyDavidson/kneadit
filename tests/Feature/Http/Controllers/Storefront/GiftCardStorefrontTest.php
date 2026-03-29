<?php

use App\Enums\GiftCardStatus;
use App\Models\GiftCard;
use App\Models\GiftCardTransaction;

beforeEach(function () {
    setUpTenantTest();
});

function makeGiftCard(array $overrides = []): GiftCard
{
    static $counter = 0;
    $counter++;

    return GiftCard::query()->create(array_merge([
        'code' => 'GIFT-TEST-' . str_pad($counter, 4, '0', STR_PAD_LEFT),
        'initial_balance' => 50.00,
        'current_balance' => 50.00,
        'purchaser_name' => 'John Doe',
        'purchaser_email' => 'john@example.com',
        'is_active' => true,
    ], $overrides));
}

test('gift card model exists', function () {
    expect(class_exists(GiftCard::class))->toBeTrue();
});

test('gift card can be created with correct balance', function () {
    $card = makeGiftCard([
        'initial_balance' => 50.00,
        'current_balance' => 50.00,
        'recipient_name' => 'Jane Doe',
        'recipient_email' => 'jane@example.com',
        'message' => 'Happy birthday!',
    ]);

    $this->assertModelExists($card);
    expect($card->current_balance)->toBe('50.00');
    expect($card->initial_balance)->toBe('50.00');
});

test('gift card is usable when active with balance', function () {
    $card = makeGiftCard();

    expect($card->is_usable)->toBeTrue();
});

test('gift card is not usable when inactive', function () {
    $card = makeGiftCard(['is_active' => false]);

    expect($card->is_usable)->toBeFalse();
});

test('gift card is not usable when depleted', function () {
    $card = makeGiftCard(['current_balance' => 0.00]);

    expect($card->is_usable)->toBeFalse();
});

test('gift card is not usable when expired', function () {
    $card = makeGiftCard(['expires_at' => now()->subDay()]);

    expect($card->is_usable)->toBeFalse();
});

test('gift card status attribute', function () {
    $active = makeGiftCard();
    $inactive = makeGiftCard(['is_active' => false]);
    $depleted = makeGiftCard(['current_balance' => 0.00]);
    $expired = makeGiftCard(['expires_at' => now()->subDay()]);

    expect($active->status)->toBe(GiftCardStatus::Active);
    expect($inactive->status)->toBe(GiftCardStatus::Inactive);
    expect($depleted->status)->toBe(GiftCardStatus::Depleted);
    expect($expired->status)->toBe(GiftCardStatus::Expired);
});

test('gift card has transactions relationship', function () {
    $card = makeGiftCard();

    GiftCardTransaction::query()->create([
        'gift_card_id' => $card->id,
        'amount' => 50.00,
        'type' => 'purchase',
        'notes' => 'Initial purchase',
        'created_at' => now(),
    ]);

    expect($card->transactions)->toHaveCount(1);
    expect($card->transactions->first()->amount)->toBe('50.00');
});
