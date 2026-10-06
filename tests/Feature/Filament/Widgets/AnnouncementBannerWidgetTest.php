<?php

use App\Filament\Widgets\AnnouncementBanner;
use App\Models\Platform\PlatformAnnouncement;
use App\Models\Staff\User;

use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('announcement banner widget can render', function () {
    livewire(AnnouncementBanner::class)
        ->assertOk();
});

test('announcement banner widget shows an active announcement again when it comes back from a serializing cache', function () {
    useSerializingCache();
    PlatformAnnouncement::factory()->create(['title' => 'Planned maintenance tonight']);

    livewire(AnnouncementBanner::class)->assertOk();
    livewire(AnnouncementBanner::class)
        ->assertOk()
        ->assertSee('Planned maintenance tonight');
});
