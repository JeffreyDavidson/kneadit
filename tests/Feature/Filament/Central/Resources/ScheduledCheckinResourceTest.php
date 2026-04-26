<?php

use App\Filament\Central\Resources\ScheduledCheckinResource;
use App\Models\Operations\ScheduledCheckin;
use App\Models\Staff\User;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    setUpCentralTest();
    actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('global search does not blow up on the scheduled-checkins table', function () {
    ScheduledCheckin::factory()->create(['name' => 'Test Welcome Checkin']);

    $results = ScheduledCheckinResource::getGlobalSearchResults('Test');

    expect($results)->not->toBeEmpty();
});
