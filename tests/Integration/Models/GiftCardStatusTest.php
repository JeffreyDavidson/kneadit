<?php

use App\Enums\GiftCardStatus;
use App\Models\GiftCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('active gift card has Active status', function () {
    $card = GiftCard::factory()->create();

    expect($card->status)->toBe(GiftCardStatus::Active);
});

test('depleted gift card has Depleted status', function () {
    $card = GiftCard::factory()->depleted()->create();

    expect($card->status)->toBe(GiftCardStatus::Depleted);
});

test('expired gift card has Expired status', function () {
    $card = GiftCard::factory()->expired()->create();

    expect($card->status)->toBe(GiftCardStatus::Expired);
});

test('inactive gift card has Inactive status', function () {
    $card = GiftCard::factory()->inactive()->create();

    expect($card->status)->toBe(GiftCardStatus::Inactive);
});

test('isUsable returns true for active card with balance', function () {
    $card = GiftCard::factory()->create();

    expect($card->is_usable)->toBeTrue();
});

test('isUsable returns false for depleted card', function () {
    $card = GiftCard::factory()->depleted()->create();

    expect($card->is_usable)->toBeFalse();
});
