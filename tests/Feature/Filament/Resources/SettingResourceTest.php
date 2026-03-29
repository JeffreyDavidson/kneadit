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

test('can list settings in the table', function () {
    $settings = Setting::factory()->count(3)->create();

    Livewire::test(ListSettings::class)
        ->assertCanSeeTableRecords($settings);
});

test('can create a setting via slide-over', function () {
    Livewire::test(ListSettings::class)
        ->callAction(CreateAction::class, data: [
            'key' => 'bakery_tagline',
            'value' => 'Fresh bread daily',
        ])
        ->assertHasNoFormErrors();

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
        ->assertHasNoFormErrors();

    expect($setting->fresh()->value)->toBe('Updated value');
});

test('create setting validates key is required', function () {
    Livewire::test(ListSettings::class)
        ->callAction(CreateAction::class, data: [
            'key' => null,
            'value' => 'test',
        ])
        ->assertHasFormErrors(['key' => 'required']);
});

test('can render setting table columns', function (string $column) {
    Setting::factory()->create();

    Livewire::test(ListSettings::class)
        ->assertCanRenderTableColumn($column);
})->with(['key', 'value']);

test('can search settings by key', function () {
    $target = Setting::factory()->create(['key' => 'store_name']);
    $other = Setting::factory()->create(['key' => 'brand_color']);

    Livewire::test(ListSettings::class)
        ->searchTable('store_name')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can sort settings by key', function () {
    $alpha = Setting::factory()->create(['key' => 'alpha_setting']);
    $zeta = Setting::factory()->create(['key' => 'zeta_setting']);

    Livewire::test(ListSettings::class)
        ->sortTable('key')
        ->assertCanSeeTableRecords(collect([$alpha, $zeta]), inOrder: true)
        ->sortTable('key', 'desc')
        ->assertCanSeeTableRecords(collect([$zeta, $alpha]), inOrder: true);
});
