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
