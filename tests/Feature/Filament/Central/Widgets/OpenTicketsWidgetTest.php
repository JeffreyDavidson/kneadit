<?php

use App\Filament\Central\Widgets\OpenTickets;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    setUpCentralTest();
    $this->actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
    Filament::bootCurrentPanel();
});

test('open tickets widget can render', function () {
    Livewire::test(OpenTickets::class)
        ->assertOk();
});
