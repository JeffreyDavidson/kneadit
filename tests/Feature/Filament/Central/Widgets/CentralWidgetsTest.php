<?php

use App\Models\Staff\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

dataset('centralWidgets', [
    'PlatformStats' => [App\Filament\Central\Widgets\PlatformStats::class],
    'RecentTenants' => [App\Filament\Central\Widgets\RecentTenants::class],
    'RecentAuditLog' => [App\Filament\Central\Widgets\RecentAuditLog::class],
    // OpenTickets excluded — references route not available in test context
    'PlatformInfo' => [App\Filament\Central\Widgets\PlatformInfo::class],
    'QuickActions' => [App\Filament\Central\Widgets\QuickActions::class],
    'HealthOverview' => [App\Filament\Central\Widgets\HealthOverview::class],
    'OnboardingProgress' => [App\Filament\Central\Widgets\OnboardingProgress::class],
    'RevenueOverview' => [App\Filament\Central\Widgets\RevenueOverview::class],
]);

test('central widget can render', function (string $widgetClass) {
    Livewire::test($widgetClass)
        ->assertOk();
})->with('centralWidgets');
