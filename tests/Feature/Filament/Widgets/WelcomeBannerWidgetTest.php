<?php

use App\Filament\Widgets\WelcomeBannerWidget;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('welcome banner widget can render', function () {
    Livewire::test(WelcomeBannerWidget::class)
        ->assertOk();
});
