<?php

use App\Filament\Resources\Settings\Pages\ListSettings;
use App\Models\Platform\Setting;
use App\Models\Staff\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('can list settings in the table', function () {
    $settings = Setting::factory()->count(3)->create();

    livewire(ListSettings::class)
        ->assertCanSeeTableRecords($settings);
});

test('can create a setting via slide-over', function () {
    livewire(ListSettings::class)
        ->callAction(CreateAction::class, data: [
            'key' => 'bakery_tagline',
            'value' => 'Fresh bread daily',
        ])
        ->assertHasNoFormErrors();

    test()->assertDatabaseHas(Setting::class, [
        'key' => 'bakery_tagline',
    ]);
});

test('can edit a setting via table action', function () {
    $setting = Setting::factory()->create();

    livewire(ListSettings::class)
        ->callAction(TestAction::make('edit')->table($setting), data: [
            'key' => $setting->key,
            'value' => 'Updated value',
        ])
        ->assertHasNoFormErrors();

    expect($setting->fresh()->value)->toBe('Updated value');
});

test('create setting validates key is required', function () {
    livewire(ListSettings::class)
        ->callAction(CreateAction::class, data: [
            'key' => null,
            'value' => 'test',
        ])
        ->assertHasFormErrors(['key' => 'required']);
});

test('can render setting table columns', function () {
    Setting::factory()->create();

    livewire(ListSettings::class)
        ->assertCanRenderTableColumn('key')
        ->assertCanRenderTableColumn('value');
});

test('can search settings by key', function () {
    $target = Setting::factory()->create(['key' => 'store_name']);
    $other = Setting::factory()->create(['key' => 'brand_color']);

    livewire(ListSettings::class)
        ->searchTable('store_name')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can sort settings by key', function () {
    $alpha = Setting::factory()->create(['key' => 'alpha_setting']);
    $zeta = Setting::factory()->create(['key' => 'zeta_setting']);

    livewire(ListSettings::class)
        ->sortTable('key')
        ->assertCanSeeTableRecords(collect([$alpha, $zeta]), inOrder: true)
        ->sortTable('key', 'desc')
        ->assertCanSeeTableRecords(collect([$zeta, $alpha]), inOrder: true);
});
