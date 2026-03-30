<?php

use App\Enums\GiftCardStatus;
use App\Models\GiftCard;
use App\Models\GiftCardTransaction;

beforeEach(function () {
    setUpTenantTest();
});

function makeGiftCard(array $overrides = []): GiftCard
{
    return GiftCard::factory()->create(array_merge([
        'initial_balance' => 50.00,
        'current_balance' => 50.00,
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
    expect($card->current_balance)->toBe('50.00')->and($card->initial_balance)->toBe('50.00');
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

    expect($active->status)->toBe(GiftCardStatus::Active)->and($inactive->status)->toBe(GiftCardStatus::Inactive)->and($depleted->status)->toBe(GiftCardStatus::Depleted)->and($expired->status)->toBe(GiftCardStatus::Expired);
});

test('gift card has transactions relationship', function () {
    $card = makeGiftCard();

    GiftCardTransaction::factory()
        ->for($card)
        ->create([
            'amount' => 50.00,
            'notes' => 'Initial purchase',
        ]);

    expect($card->transactions)->toHaveCount(1)->and($card->transactions->first()->amount)->toBe('50.00');
});
