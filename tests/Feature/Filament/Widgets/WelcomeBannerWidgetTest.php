<?php

use App\Filament\Widgets\WelcomeBannerWidget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
});

test('welcome banner widget can render', function () {
    Livewire::test(WelcomeBannerWidget::class)
        ->assertOk();
});
