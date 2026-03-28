<?php

use App\Filament\Pages\ManageSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
});

test('manage settings page can save store name', function () {
    Livewire::test(ManageSettings::class)
        ->set('store_name', 'New Bakery Name')
        ->call('save');

    expect(settings('store_name'))->toBe('New Bakery Name');
});
