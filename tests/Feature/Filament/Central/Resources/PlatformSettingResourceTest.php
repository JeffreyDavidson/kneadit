<?php

use App\Filament\Central\Resources\PlatformSettings\Pages\ListPlatformSettings;
use App\Models\Platform\PlatformSetting;
use App\Models\Staff\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    setUpCentralTest();
    actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('list page renders', function () {
    Livewire::test(ListPlatformSettings::class)->assertOk();
});

test('list shows existing settings', function () {
    PlatformSetting::query()->create(['key' => 'maintenance_mode', 'value' => '0']);
    PlatformSetting::query()->create(['key' => 'maintenance_message', 'value' => 'Back soon']);

    Livewire::test(ListPlatformSettings::class)
        ->assertCanSeeTableRecords(PlatformSetting::all());
});

test('create stores a new setting', function () {
    Livewire::test(ListPlatformSettings::class)
        ->callAction('create', data: [
            'key' => 'feature_x_enabled',
            'value' => '1',
        ])
        ->assertHasNoActionErrors();

    expect(PlatformSetting::query()->where('key', 'feature_x_enabled')->value('value'))->toBe('1');
});

test('create rejects duplicate key', function () {
    PlatformSetting::query()->create(['key' => 'duplicate_key', 'value' => 'first']);

    Livewire::test(ListPlatformSettings::class)
        ->callAction('create', data: [
            'key' => 'duplicate_key',
            'value' => 'second',
        ])
        ->assertHasActionErrors();

    // Original value untouched
    expect(PlatformSetting::query()->where('key', 'duplicate_key')->value('value'))->toBe('first');
});
