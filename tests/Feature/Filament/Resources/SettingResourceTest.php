<?php

use App\Filament\Resources\Settings\Pages\ListSettings;
use App\Models\Setting;
use App\Models\User;
use Filament\Actions\CreateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
});

test('can create a setting via slide-over', function () {
    Livewire::test(ListSettings::class)
        ->callAction(CreateAction::class, data: [
            'key' => 'bakery_tagline',
            'value' => 'Fresh bread daily',
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(Setting::class, [
        'key' => 'bakery_tagline',
    ]);
});

test('can edit a setting via table action', function () {
    $setting = Setting::factory()->create();

    Livewire::test(ListSettings::class)
        ->callTableAction('edit', $setting, data: [
            'key' => $setting->key,
            'value' => 'Updated value',
        ])
        ->assertHasNoTableActionErrors();

    expect($setting->fresh()->value)->toBe('Updated value');
});

test('create setting validates key is required', function () {
    Livewire::test(ListSettings::class)
        ->callAction(CreateAction::class, data: [
            'key' => null,
            'value' => 'test',
        ])
        ->assertHasActionErrors(['key' => 'required']);
});
