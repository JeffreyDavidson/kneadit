<?php

use App\Filament\Resources\BlogPosts\Pages\ListBlogPosts;
use App\Filament\Resources\EmailCampaigns\Pages\ListEmailCampaigns;
use App\Models\User;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

beforeEach(function () {
    setUpCentralTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
    Feature::define('growth-features', fn () => true);
});

test('BlogPosts list page can render', function () {
    Livewire::test(ListBlogPosts::class)
        ->assertOk();
});

test('EmailCampaigns list page can render', function () {
    Livewire::test(ListEmailCampaigns::class)
        ->assertOk();
});
