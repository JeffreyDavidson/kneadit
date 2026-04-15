<?php

use App\Enums\Engagement\RewardType;
use App\Filament\Resources\LoyaltyRewards\Pages\ListLoyaltyRewards;
use App\Models\Engagement\LoyaltyReward;
use App\Models\Staff\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('can list loyalty rewards in the table', function () {
    $rewards = LoyaltyReward::factory()->count(3)->create();

    Livewire::test(ListLoyaltyRewards::class)
        ->assertCanSeeTableRecords($rewards);
});

test('can create a loyalty reward via slide-over', function () {
    Livewire::test(ListLoyaltyRewards::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Free Cookie',
            'points_required' => 100,
            'reward_type' => RewardType::PercentageDiscount->value,
            'reward_value' => 15,
        ])
        ->assertHasNoFormErrors();

    test()->assertDatabaseHas(LoyaltyReward::class, [
        'name' => 'Free Cookie',
        'points_required' => 100,
    ]);
});

test('can edit a loyalty reward via table action', function () {
    $reward = LoyaltyReward::factory()->create();

    Livewire::test(ListLoyaltyRewards::class)
        ->callAction(TestAction::make('edit')->table($reward), data: [
            'name' => 'Updated Reward',
            'points_required' => $reward->points_required,
            'reward_type' => $reward->reward_type->value,
            'reward_value' => $reward->reward_value,
        ])
        ->assertHasNoFormErrors();

    expect($reward->fresh()->name)->toBe('Updated Reward');
});

test('create loyalty reward validates required fields', function (array $data, array $errors) {
    Livewire::test(ListLoyaltyRewards::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Test',
            'points_required' => 100,
            'reward_type' => RewardType::PercentageDiscount->value,
            'reward_value' => 10,
            ...$data,
        ])
        ->assertHasFormErrors($errors);
})->with([
    'name is required' => [['name' => null], ['name' => 'required']],
    'points required is required' => [['points_required' => null], ['points_required' => 'required']],
    'reward type is required' => [['reward_type' => null], ['reward_type' => 'required']],
    'reward value is required' => [['reward_value' => null], ['reward_value' => 'required']],
]);

test('can render loyalty reward table columns', function (string $column) {
    LoyaltyReward::factory()->create();

    Livewire::test(ListLoyaltyRewards::class)
        ->assertCanRenderTableColumn($column);
})->with(['name', 'points_required', 'reward_type', 'reward_value']);

test('can search loyalty rewards by name', function () {
    $target = LoyaltyReward::factory()->create(['name' => 'Free Cookie']);
    $other = LoyaltyReward::factory()->create(['name' => 'Discount Bread']);

    Livewire::test(ListLoyaltyRewards::class)
        ->searchTable('Cookie')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can filter loyalty rewards by reward type', function () {
    $percentage = LoyaltyReward::factory()->create(['reward_type' => RewardType::PercentageDiscount]);
    $fixed = LoyaltyReward::factory()->create(['reward_type' => RewardType::FixedDiscount]);

    Livewire::test(ListLoyaltyRewards::class)
        ->filterTable('reward_type', RewardType::PercentageDiscount->value)
        ->assertCanSeeTableRecords(collect([$percentage]))
        ->assertCanNotSeeTableRecords(collect([$fixed]));
});

test('can filter loyalty rewards by active status', function () {
    $active = LoyaltyReward::factory()->create(['is_active' => true]);
    $inactive = LoyaltyReward::factory()->inactive()->create();

    Livewire::test(ListLoyaltyRewards::class)
        ->filterTable('is_active', true)
        ->assertCanSeeTableRecords(collect([$active]))
        ->assertCanNotSeeTableRecords(collect([$inactive]));
});

test('can sort loyalty rewards by points required', function () {
    $low = LoyaltyReward::factory()->create(['points_required' => 50]);
    $high = LoyaltyReward::factory()->create(['points_required' => 500]);

    Livewire::test(ListLoyaltyRewards::class)
        ->sortTable('points_required')
        ->assertCanSeeTableRecords(collect([$low, $high]), inOrder: true)
        ->sortTable('points_required', 'desc')
        ->assertCanSeeTableRecords(collect([$high, $low]), inOrder: true);
});

test('resource returns globally searchable attributes', function () {
    expect(App\Filament\Resources\LoyaltyRewards\LoyaltyRewardResource::getGloballySearchableAttributes())
        ->toBe(['name']);
});

test('resource returns global search result title', function () {
    $reward = LoyaltyReward::factory()->create(['name' => 'Free Cookie']);

    expect(App\Filament\Resources\LoyaltyRewards\LoyaltyRewardResource::getGlobalSearchResultTitle($reward))
        ->toBe('Free Cookie');
});

test('resource returns global search result details', function () {
    $reward = LoyaltyReward::factory()->create([
        'points_required' => 200,
    ]);

    $details = App\Filament\Resources\LoyaltyRewards\LoyaltyRewardResource::getGlobalSearchResultDetails($reward);

    expect($details)
        ->toHaveKey('Points', '200')
        ->toHaveKey('Type');
});
