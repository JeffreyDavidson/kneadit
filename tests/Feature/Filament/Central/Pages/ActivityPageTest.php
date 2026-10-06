<?php

use App\Filament\Central\Pages\Activity;
use App\Models\Platform\AdminAuditLog;
use App\Models\Platform\PlatformActivity;
use App\Models\Staff\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('activity page renders platform events created by the factory', function () {
    PlatformActivity::factory()->create(['event' => 'tenant_created', 'description' => 'Sunrise Bakery signed up']);

    livewire(Activity::class)
        ->assertOk()
        ->assertSee('Sunrise Bakery signed up');
});

test('activity page renders admin actions created by the factory', function () {
    AdminAuditLog::factory()->create(['action' => 'created_tenant', 'description' => 'Created Sunrise Bakery']);

    livewire(Activity::class)
        ->set('activeTab', 'audit')
        ->assertOk()
        ->assertSee('Created Sunrise Bakery');
});
