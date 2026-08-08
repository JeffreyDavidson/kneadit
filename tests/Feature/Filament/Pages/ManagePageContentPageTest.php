<?php

use App\Filament\Pages\Settings\ManagePageContent;
use App\Models\Staff\User;
use App\Services\Settings\SettingsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('manage page content renders and saves', function () {
    Livewire::test(ManagePageContent::class)
        ->assertOk()
        ->call('save');
});

test('reset to saved action restores the last saved page content', function () {
    resolve(SettingsManager::class)->set('page_content', json_encode([
        'menu' => ['hero_title' => 'Saved Menu Title'],
    ]));

    Livewire::test(ManagePageContent::class)
        ->set('pageContent.menu.hero_title', 'Unsaved edit')
        ->callAction('resetToSaved')
        ->assertSet('pageContent.menu.hero_title', 'Saved Menu Title');
});
