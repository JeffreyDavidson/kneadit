<?php

use App\Filament\Central\Widgets\NeedsAttention;
use App\Filament\Central\Widgets\OnboardingProgress;
use App\Filament\Central\Widgets\PlatformStats;
use App\Filament\Central\Widgets\QuickActions;
use App\Filament\Central\Widgets\RecentAuditLog;
use App\Filament\Central\Widgets\RecentTenants;
use App\Filament\Central\Widgets\RevenueOverview;
use App\Models\Platform\SupportTicket;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

dataset('centralWidgets', [
    'PlatformStats' => [PlatformStats::class],
    'RecentTenants' => [RecentTenants::class],
    'RecentAuditLog' => [RecentAuditLog::class],
    'QuickActions' => [QuickActions::class],
    'NeedsAttention' => [NeedsAttention::class],
    'OnboardingProgress' => [OnboardingProgress::class],
    'RevenueOverview' => [RevenueOverview::class],
]);

test('central widget can render', function (string $widgetClass) {
    livewire($widgetClass)
        ->assertOk();
})->with('centralWidgets');

test('needs attention widget renders Filament icons for inbox alerts', function () {
    SupportTicket::factory()->open()->create();

    livewire(NeedsAttention::class)
        ->assertOk()
        ->assertSee('Open Inbox')
        ->assertSee('awaiting reply');
});

test('needs attention widget counts paused bakeries by paused_at, not by the storefront setting', function () {
    Tenant::factory()->count(2)->create(['paused_at' => now()->subDay()]);
    Tenant::factory()->create(['storefront_enabled' => false, 'external_website' => 'https://own-site.example.com']);

    livewire(NeedsAttention::class)
        ->assertSee('2 paused bakeries');
});

test('needs attention widget shows no paused item while nothing is paused', function () {
    Tenant::factory()->create(['storefront_enabled' => false]);

    livewire(NeedsAttention::class)
        ->assertDontSee('paused');
});

test('platform stats widget renders when daily ticket counts are integers', function () {
    SupportTicket::factory()->open()->create([
        'created_at' => now()->subDay(),
    ]);

    livewire(PlatformStats::class)
        ->assertOk();
});

test('revenue overview widget renders again when its figures come back from a serializing cache', function () {
    useSerializingCache();

    livewire(RevenueOverview::class)->assertOk();
    livewire(RevenueOverview::class)
        ->assertOk()
        ->assertSee('ARPU');
});
