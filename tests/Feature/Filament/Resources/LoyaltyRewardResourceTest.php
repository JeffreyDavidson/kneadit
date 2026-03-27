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
