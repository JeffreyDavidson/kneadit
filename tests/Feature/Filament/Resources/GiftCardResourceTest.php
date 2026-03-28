<?php

use App\Filament\Resources\GiftCards\Pages\ListGiftCards;
use App\Models\GiftCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);
});

test('can list gift cards in the table', function () {
    $giftCards = GiftCard::factory()->count(3)->create();

    Livewire::test(ListGiftCards::class)
        ->assertCanSeeTableRecords($giftCards);
});

test('can search gift cards by code', function () {
    $target = GiftCard::factory()->create(['code' => 'ABCD-1234-EFGH-5678']);
    $other = GiftCard::factory()->create(['code' => 'ZZZZ-9999-YYYY-0000']);

    Livewire::test(ListGiftCards::class)
        ->searchTable('ABCD')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can edit a gift card via table action', function () {
    $giftCard = GiftCard::factory()->create();

    Livewire::test(ListGiftCards::class)
        ->callTableAction('edit', $giftCard, data: [
            'purchaser_name' => 'Updated Name',
            'purchaser_email' => $giftCard->purchaser_email,
            'initial_balance' => $giftCard->initial_balance,
        ])
        ->assertHasNoTableActionErrors();

    expect($giftCard->fresh()->purchaser_name)->toBe('Updated Name');
});

test('can render gift card table columns', function (string $column) {
    GiftCard::factory()->create();

    Livewire::test(ListGiftCards::class)
        ->assertCanRenderTableColumn($column);
})->with(['code', 'purchaser_name', 'current_balance']);

test('can sort gift cards by current balance', function () {
    $low = GiftCard::factory()->create(['initial_balance' => 10, 'current_balance' => 10]);
    $high = GiftCard::factory()->create(['initial_balance' => 100, 'current_balance' => 100]);

    Livewire::test(ListGiftCards::class)
        ->sortTable('current_balance')
        ->assertCanSeeTableRecords(collect([$low, $high]), inOrder: true)
        ->sortTable('current_balance', 'desc')
        ->assertCanSeeTableRecords(collect([$high, $low]), inOrder: true);
});

test('edit gift card validates required fields', function (array $data, array $errors) {
    $giftCard = GiftCard::factory()->create();

    Livewire::test(ListGiftCards::class)
        ->callTableAction('edit', $giftCard, data: [
            'purchaser_name' => $giftCard->purchaser_name,
            'purchaser_email' => $giftCard->purchaser_email,
            'initial_balance' => $giftCard->initial_balance,
            ...$data,
        ])
        ->assertHasTableActionErrors($errors);
})->with([
    'purchaser name is required' => [['purchaser_name' => null], ['purchaser_name' => 'required']],
    'purchaser email is required' => [['purchaser_email' => null], ['purchaser_email' => 'required']],
    'initial balance is required' => [['initial_balance' => null], ['initial_balance' => 'required']],
]);

test('can create a gift card via header action', function () {
    Livewire::test(ListGiftCards::class)
        ->callAction('create', data: [
            'purchaser_name' => 'Jane Doe',
            'purchaser_email' => 'jane@example.com',
            'initial_balance' => 50.00,
        ])
        ->assertHasNoActionErrors();

    $giftCard = GiftCard::query()->first();
    expect($giftCard)
        ->purchaser_name->toBe('Jane Doe')
        ->purchaser_email->toBe('jane@example.com')
        ->initial_balance->toBe('50.00')
        ->code->not->toBeNull();
});

test('can filter gift cards by depleted status', function () {
    $active = GiftCard::factory()->create();
    $depleted = GiftCard::factory()->depleted()->create();

    Livewire::test(ListGiftCards::class)
        ->filterTable('status', App\Enums\GiftCardStatus::Depleted->value)
        ->assertCanSeeTableRecords(collect([$depleted]))
        ->assertCanNotSeeTableRecords(collect([$active]));
});
