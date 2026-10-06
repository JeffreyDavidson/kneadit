<?php

use App\Filament\Pages\Settings\ManageSettings;
use App\Models\Staff\User;
use Database\Seeders\Operations\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('the seeded store settings pass the Settings page validation', function () {
    (new SettingSeeder)->run();

    livewire(ManageSettings::class)
        ->call('save')
        ->assertHasNoErrors();
});

test('the seeded bakery name matches the demo tenant name', function () {
    (new SettingSeeder)->run();

    expect(settings('store_name'))->toBe('Sweet Dreams Bakery');
});
