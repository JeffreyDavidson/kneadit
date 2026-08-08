<?php

use App\Filament\Pages\Settings\AdminAppearance;
use App\Models\Staff\User;
use App\Services\Settings\SettingsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('admin appearance page renders for manager', function () {
    $component = livewire(AdminAppearance::class);

    $component->assertOk();
    $component->assertSet('current', 'honey');
});

test('selecting a valid theme persists it and redirects', function () {
    livewire(AdminAppearance::class)
        ->call('selectTheme', 'slate')
        ->assertSet('current', 'slate')
        ->assertRedirect();

    expect(resolve(SettingsManager::class)->get('admin_theme'))->toBe('slate');
});

test('selecting an unknown theme is ignored', function () {
    resolve(SettingsManager::class)->set('admin_theme', 'honey');

    livewire(AdminAppearance::class)
        ->call('selectTheme', 'not-a-real-theme')
        ->assertSet('current', 'honey');

    expect(resolve(SettingsManager::class)->get('admin_theme'))->toBe('honey');
});
