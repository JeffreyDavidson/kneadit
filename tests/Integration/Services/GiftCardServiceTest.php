<?php

use App\DataTransferObjects\CreateGiftCardData;
use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Services\GiftCardService;
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
    $card1 = test()->service->create(CreateGiftCardData::fromArray([
        'initial_balance' => 50,
        'purchaser_name' => 'Alice',
        'purchaser_email' => 'alice@test.com',
    ]));
    $card2 = test()->service->create(CreateGiftCardData::fromArray([
        'initial_balance' => 25,
        'purchaser_name' => 'Bob',
        'purchaser_email' => 'bob@test.com',
    ]));

    expect($card1->code)->not->toBe($card2->code);
});

test('check balance returns correct card', function () {
    $card = test()->service->create(CreateGiftCardData::fromArray([
        'initial_balance' => 100,
        'purchaser_name' => 'Test',
        'purchaser_email' => 'test@test.com',
    ]));

    $found = test()->service->checkBalance($card->code);

    expect($found)->not->toBeNull()->and((float) $found->current_balance)->toBe(100.0);
});

test('redeem deducts from balance', function () {
    $card = test()->service->create(CreateGiftCardData::fromArray([
        'initial_balance' => 50,
        'purchaser_name' => 'Test',
        'purchaser_email' => 'test@test.com',
    ]));

    $result = test()->service->redeem($card->code, 20);

    expect($result->success)->toBeTrue()->and($result->amountApplied)->toBe(20.0)->and($result->remainingBalance)->toBe(30.0);
});

test('redeem creates transaction record', function () {
    $card = test()->service->create(CreateGiftCardData::fromArray([
        'initial_balance' => 50,
        'purchaser_name' => 'Test',
        'purchaser_email' => 'test@test.com',
    ]));

    Mail::fake();
    $user = User::query()->create(['name' => 'Test', 'email' => 'u@t.com', 'password' => bcrypt('p')]);
    $customer = Customer::query()->create(['name' => 'C', 'email' => 'c@t.com']);
    $order = Order::query()->create(['user_id' => $user->id, 'customer_id' => $customer->id, 'status' => OrderStatus::Pending, 'total' => 15, 'subtotal' => 15]);

    test()->service->redeem($card->code, 15, $order->id);

    assertDatabaseHas('gift_card_transactions', [
        'gift_card_id' => $card->id,
        'amount' => -15.00,
        'type' => 'redemption',
        'order_id' => $order->id,
    ]);
});

test('redeem caps at available balance', function () {
    $card = test()->service->create(CreateGiftCardData::fromArray([
        'initial_balance' => 20,
        'purchaser_name' => 'Test',
        'purchaser_email' => 'test@test.com',
    ]));

    $result = test()->service->redeem($card->code, 50);

    expect($result->success)->toBeTrue()->and($result->amountApplied)->toBe(20.0)->and($result->remainingBalance)->toBe(0.0);
});

test('redeem fails when card inactive', function () {
    $card = test()->service->create(CreateGiftCardData::fromArray([
        'initial_balance' => 50,
        'purchaser_name' => 'Test',
        'purchaser_email' => 'test@test.com',
    ]));
    $card->update(['is_active' => false]);

    $result = test()->service->redeem($card->code, 10);

    expect($result->success)->toBeFalse();
});

test('redeem fails when card expired', function () {
    $card = test()->service->create(CreateGiftCardData::fromArray([
        'initial_balance' => 50,
        'purchaser_name' => 'Test',
        'purchaser_email' => 'test@test.com',
        'expires_at' => now()->subDay(),
    ]));

    $result = test()->service->redeem($card->code, 10);

    expect($result->success)->toBeFalse();
});
