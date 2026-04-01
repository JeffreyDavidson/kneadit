<?php

use App\Filament\Pages\Dashboard\DashboardConfig;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
});

test('dashboard config renders and saves', function () {
    Livewire::test(DashboardConfig::class)
        ->assertOk()
        ->call('save');

    expect(settings('dashboard_widgets'))->not->toBeNull();
});
