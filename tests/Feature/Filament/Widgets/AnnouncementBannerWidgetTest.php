<?php

use App\Filament\Widgets\AnnouncementBanner;
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
