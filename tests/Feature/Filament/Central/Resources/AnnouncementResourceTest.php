<?php

use App\Filament\Central\Resources\AnnouncementResource\Pages\ListAnnouncements;
use App\Models\Platform\PlatformAnnouncement;
use App\Models\Staff\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    $user = User::factory()->platformAdmin()->create();
    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('can filter announcements by active status', function () {
    $active = PlatformAnnouncement::factory()->active()->create();
    $inactive = PlatformAnnouncement::factory()->inactive()->create();

    livewire(ListAnnouncements::class)
        ->filterTable('is_active', true)
        ->assertCanSeeTableRecords(collect([$active]))
        ->assertCanNotSeeTableRecords(collect([$inactive]));
});
