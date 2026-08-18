<?php

use App\Actions\GiftCards\CreateGiftCard;
use App\Actions\GiftCards\RedeemGiftCard;
use App\DataTransferObjects\GiftCards\CreateGiftCardData;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Services\GiftCards\GiftCardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();

    test()->service = new GiftCardService;
});

test('generate code creates formatted code', function () {
    $code = test()->service->generateCode();

    expect($code)->toMatch('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/');
});

test('create generates unique codes', function () {
    $card1 = resolve(CreateGiftCard::class)(CreateGiftCardData::fromArray([
        'initial_balance' => 50,
        'purchaser_name' => 'Alice',
        'purchaser_email' => 'alice@test.com',
    ]));
    $card2 = resolve(CreateGiftCard::class)(CreateGiftCardData::fromArray([
        'initial_balance' => 25,
        'purchaser_name' => 'Bob',
        'purchaser_email' => 'bob@test.com',
    ]));

    expect($card1->code)->not->toBe($card2->code);
});

test('check balance returns correct card', function () {
    $card = resolve(CreateGiftCard::class)(CreateGiftCardData::fromArray([
        'initial_balance' => 100,
        'purchaser_name' => 'Test',
        'purchaser_email' => 'test@test.com',
    ]));

    $found = test()->service->checkBalance($card->code);

    expect($found)->not->toBeNull()->and($found->current_balance->dollars())->toBe(100.0);
});

test('redeem deducts from balance', function () {
    $card = resolve(CreateGiftCard::class)(CreateGiftCardData::fromArray([
        'initial_balance' => 50,
        'purchaser_name' => 'Test',
        'purchaser_email' => 'test@test.com',
    ]));

    $result = resolve(RedeemGiftCard::class)($card->code, 20);

    expect($result->success)->toBeTrue()->and($result->amountApplied)->toBe(20.0)->and($result->remainingBalance)->toBe(30.0);
});

test('redeem creates transaction record', function () {
    $card = resolve(CreateGiftCard::class)(CreateGiftCardData::fromArray([
        'initial_balance' => 50,
        'purchaser_name' => 'Test',
        'purchaser_email' => 'test@test.com',
    ]));

    Mail::fake();
    $user = User::factory()->owner()->create();
    $customer = Customer::factory()->create();
    $order = Order::factory()
        ->for($customer)
        ->recycle($user)
        ->create(['total' => 15, 'subtotal' => 15]);

    resolve(RedeemGiftCard::class)($card->code, 15, $order->id);

    assertDatabaseHas('gift_card_transactions', [
        'gift_card_id' => $card->id,
        // gift_card_transactions.amount is bigint cents (migration 2026_04_22_223000).
        'amount' => -1500,
        'type' => 'redemption',
        'order_id' => $order->id,
    ]);
});

test('redeem caps at available balance', function () {
    $card = resolve(CreateGiftCard::class)(CreateGiftCardData::fromArray([
        'initial_balance' => 20,
        'purchaser_name' => 'Test',
        'purchaser_email' => 'test@test.com',
    ]));

    $result = resolve(RedeemGiftCard::class)($card->code, 50);

    expect($result->success)->toBeTrue()->and($result->amountApplied)->toBe(20.0)->and($result->remainingBalance)->toBe(0.0);
});

test('redeem fails when card inactive', function () {
    $card = resolve(CreateGiftCard::class)(CreateGiftCardData::fromArray([
        'initial_balance' => 50,
        'purchaser_name' => 'Test',
        'purchaser_email' => 'test@test.com',
    ]));
    $card->update(['is_active' => false]);

    $result = resolve(RedeemGiftCard::class)($card->code, 10);

    expect($result->success)->toBeFalse();
});

test('redeem fails when card expired', function () {
    $card = resolve(CreateGiftCard::class)(CreateGiftCardData::fromArray([
        'initial_balance' => 50,
        'purchaser_name' => 'Test',
        'purchaser_email' => 'test@test.com',
        'expires_at' => now()->subDay(),
    ]));

    $result = resolve(RedeemGiftCard::class)($card->code, 10);

    expect($result->success)->toBeFalse();
});
