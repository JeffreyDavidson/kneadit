<?php

use App\Filament\Pages\Engagement\AnnouncementBanner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
});

test('staff cannot access announcement banner page', function () {
    $this->actingAs(User::factory()->staff()->create());

    expect(AnnouncementBanner::canAccess())->toBeFalse();
});

test('announcement banner saves settings', function () {
    Livewire::test(AnnouncementBanner::class)
        ->set('announcement_enabled', true)
        ->set('announcement_text', 'We are closed for holidays!')
        ->call('save');

    expect(settings('announcement_text'))->toBe('We are closed for holidays!')
        ->and(settings('announcement_enabled'))->toBe('1');
});
