<?php

use App\Filament\Widgets\AnnouncementBanner;
use App\Models\Staff\User;
use Livewire\Livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('announcement banner widget can render', function () {
    Livewire::test(AnnouncementBanner::class)
        ->assertOk();
});
