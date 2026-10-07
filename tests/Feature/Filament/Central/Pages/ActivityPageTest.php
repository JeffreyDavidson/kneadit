<?php

use App\Filament\Central\Pages\Activity;
use App\Models\Platform\AdminAuditLog;
use App\Models\Staff\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('activity page renders admin actions created by the factory', function () {
    AdminAuditLog::factory()->create(['action' => 'created_tenant', 'description' => 'Created Sunrise Bakery']);

    livewire(Activity::class)
        ->assertOk()
        ->assertSee('Created Sunrise Bakery');
});
