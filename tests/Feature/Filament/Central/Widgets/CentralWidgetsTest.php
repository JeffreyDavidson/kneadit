<?php

use App\Filament\Central\Widgets\NeedsAttention;
use App\Filament\Central\Widgets\OnboardingProgress;
use App\Filament\Central\Widgets\PlatformStats;
use App\Filament\Central\Widgets\QuickActions;
use App\Filament\Central\Widgets\RecentAuditLog;
use App\Filament\Central\Widgets\RecentTenants;
use App\Filament\Central\Widgets\RevenueOverview;
use App\Models\Staff\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

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
    Livewire::test($widgetClass)
        ->assertOk();
})->with('centralWidgets');
