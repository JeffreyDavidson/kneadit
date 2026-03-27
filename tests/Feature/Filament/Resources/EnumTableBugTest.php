<?php

use App\Filament\Resources\CateringInquiries\Pages\ListCateringInquiries;
use App\Filament\Resources\Incomes\Pages\ListIncomes;
use App\Filament\Resources\LoyaltyRewards\Pages\ListLoyaltyRewards;
use App\Filament\Resources\SocialPosts\Pages\ListSocialPosts;
use App\Models\CateringInquiry;
use App\Models\Income;
use App\Models\LoyaltyReward;
use App\Models\SocialPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
    Feature::define('growth-features', fn () => true);
});

test('CateringInquiries table renders with records', function () {
    CateringInquiry::factory()->create();

    Livewire::test(ListCateringInquiries::class)
        ->assertOk();
});

test('Incomes table renders with records', function () {
    Income::factory()->create();

    Livewire::test(ListIncomes::class)
        ->assertOk();
});

test('LoyaltyRewards table renders with records', function () {
    LoyaltyReward::factory()->create();

    Livewire::test(ListLoyaltyRewards::class)
        ->assertOk();
});

test('SocialPosts table renders with records', function () {
    SocialPost::factory()->create();

    Livewire::test(ListSocialPosts::class)
        ->assertOk();
});
