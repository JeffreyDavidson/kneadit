<?php

use App\Filament\Pages\Settings\HomepageBuilder;
use App\Models\Staff\User;
use App\Services\Settings\SettingsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('homepage builder renders and saves', function () {
    Livewire::test(HomepageBuilder::class)
        ->assertOk()
        ->call('save');

    expect(settings('homepage_sections'))->not->toBeNull();
});

test('reset to defaults action restores defaults', function () {
    resolve(SettingsManager::class)->setMany([
        'hero_tagline' => 'Custom tagline that should get overwritten',
    ]);

    Livewire::test(HomepageBuilder::class)
        ->callAction('resetToDefaults');

    expect(settings('hero_tagline'))->toBe('Where every bite tells a story');
});
