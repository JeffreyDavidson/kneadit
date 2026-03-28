<?php

use App\Models\User;
use Filament\Facades\Filament;

beforeEach(function () {
    setUpCentralTest();
    $this->actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
    Filament::bootCurrentPanel();
});

// Central panel routes not fully registered in test context
test('open tickets widget can render')
    ->todo();
