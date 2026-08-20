<?php

use App\Enums\Financial\GiftCardStatus;
use App\Models\Financial\GiftCard;
use App\Models\Financial\GiftCardTransaction;

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

test('gift cards controller passes settings and content to view', function () {
    $response = test()
        ->withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.giftCards', [], false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content');
});

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
    expect($card->current_balance->dollars())->toBe(50.00)->and($card->initial_balance->dollars())->toBe(50.00);
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

test('GiftCardStatus::resolve derives state from is_active, expires_at, and current_balance', function () {
    $active = makeGiftCard();
    $inactive = makeGiftCard(['is_active' => false]);
    $depleted = makeGiftCard(['current_balance' => 0.00]);
    $expired = makeGiftCard(['expires_at' => now()->subDay()]);

    expect(GiftCardStatus::resolve($active))->toBe(GiftCardStatus::Active)
        ->and(GiftCardStatus::resolve($inactive))->toBe(GiftCardStatus::Inactive)
        ->and(GiftCardStatus::resolve($depleted))->toBe(GiftCardStatus::Depleted)
        ->and(GiftCardStatus::resolve($expired))->toBe(GiftCardStatus::Expired);
});

test('gift card has transactions relationship', function () {
    $card = makeGiftCard();

    GiftCardTransaction::factory()
        ->for($card)
        ->create([
            'amount' => 50.00,
            'notes' => 'Initial purchase',
        ]);

    expect($card->transactions)->toHaveCount(1)->and($card->transactions->firstOrFail()->amount->dollars())->toBe(50.00);
});
