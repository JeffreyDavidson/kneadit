<?php

use App\Enums\RewardType;
use App\Filament\Resources\LoyaltyRewards\Pages\ListLoyaltyRewards;
use App\Models\LoyaltyReward;
use App\Models\User;
use Filament\Actions\CreateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('can create a loyalty reward via slide-over', function () {
    Livewire::test(ListLoyaltyRewards::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Free Cookie',
            'points_required' => 100,
            'reward_type' => RewardType::PercentageDiscount->value,
            'reward_value' => 15,
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(LoyaltyReward::class, [
        'name' => 'Free Cookie',
        'points_required' => 100,
    ]);
});

test('can edit a loyalty reward via table action', function () {
    $reward = LoyaltyReward::factory()->create();

    Livewire::test(ListLoyaltyRewards::class)
        ->callTableAction('edit', $reward, data: [
            'name' => 'Updated Reward',
            'points_required' => $reward->points_required,
            'reward_type' => $reward->reward_type->value,
            'reward_value' => $reward->reward_value,
        ])
        ->assertHasNoTableActionErrors();

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
        ->assertHasActionErrors($errors);
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
