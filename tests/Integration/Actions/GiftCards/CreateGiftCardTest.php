<?php

use App\Actions\GiftCards\CreateGiftCard;
use App\DataTransferObjects\GiftCards\CreateGiftCardData;
use App\Models\Financial\GiftCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('creates a gift card with initial balance and transaction', function () {
    $data = CreateGiftCardData::fromArray([
        'purchaser_name' => 'Jane Doe',
        'purchaser_email' => 'jane@example.com',
        'initial_balance' => 50.00,
    ]);

    $card = resolve(CreateGiftCard::class)($data);

    expect($card)
        ->toBeInstanceOf(GiftCard::class)
        ->initial_balance->toEqual(50.0)
        ->current_balance->toEqual(50.0)
        ->purchaser_name->toBe('Jane Doe')->and($card->transactions)->toHaveCount(1);
});
