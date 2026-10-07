<?php

use App\Filament\Central\Pages\Activity;
use App\Filament\Central\Pages\FeatureUsage;
use App\Filament\Central\Pages\MaintenanceMode;
use App\Filament\Central\Resources\AnnouncementResource;
use App\Filament\Central\Resources\AnnouncementResource\Pages\ListAnnouncements;
use App\Filament\Central\Widgets\QuickActions;
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

test('half-built central pages cannot be reached', function (string $page) {
    expect($page::canAccess())->toBeFalse()
        ->and($page::shouldRegisterNavigation())->toBeFalse();

    livewire($page)
        ->assertForbidden();
})->with([
    'maintenance mode' => MaintenanceMode::class,
    'feature usage' => FeatureUsage::class,
]);

test('the announcements resource cannot be reached', function () {
    expect(AnnouncementResource::canViewAny())->toBeFalse()
        ->and(AnnouncementResource::shouldRegisterNavigation())->toBeFalse();

    livewire(ListAnnouncements::class)
        ->assertForbidden();
});

test('half-built features are missing from the central navigation', function (string $label) {
    $labels = collect(Filament::getNavigation())
        ->flatMap(fn ($group): array => $group->getItems()->map(fn ($item): string => $item->getLabel())->all())
        ->all();

    expect($labels)->not->toContain($label);
})->with([
    'Maintenance Mode',
    'Feature Usage',
    'Announcements',
]);

test('the dashboard quick actions do not link to maintenance mode', function () {
    livewire(QuickActions::class)
        ->assertOk()
        ->assertDontSee('admin/maintenance-mode');
});

test('the activity page only offers the admin actions tab', function () {
    PlatformActivity::factory()->create(['event' => 'tenant_created', 'description' => 'Sunrise Bakery signed up']);
    AdminAuditLog::factory()->create(['action' => 'created_tenant', 'description' => 'Created Sunrise Bakery']);

    livewire(Activity::class)
        ->assertOk()
        ->assertSet('activeTab', 'audit')
        ->assertDontSee('Platform Events')
        ->assertSee('Created Sunrise Bakery');
});

test('the activity page ignores a request for the platform events tab', function () {
    PlatformActivity::factory()->create(['event' => 'tenant_created', 'description' => 'Sunrise Bakery signed up']);

    livewire(Activity::class)
        ->set('activeTab', 'platform')
        ->assertOk()
        ->assertSet('activeTab', 'audit')
        ->assertDontSee('Sunrise Bakery signed up');
});
