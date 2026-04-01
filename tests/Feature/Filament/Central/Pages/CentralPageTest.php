<?php

use App\Models\Staff\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    setUpCentralTest();
    $this->actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

dataset('centralPages', [
    'Dashboard' => [App\Filament\Central\Pages\Dashboard::class],
    'Activity' => [App\Filament\Central\Pages\Activity::class],
    'Analytics' => [App\Filament\Central\Pages\Analytics::class],
    'BakeryInsights' => [App\Filament\Central\Pages\BakeryInsights::class],
    'DataExport' => [App\Filament\Central\Pages\DataExport::class],
    'FeatureUsage' => [App\Filament\Central\Pages\FeatureUsage::class],
    'MaintenanceMode' => [App\Filament\Central\Pages\MaintenanceMode::class],
    'OnboardingTracker' => [App\Filament\Central\Pages\OnboardingTracker::class],
    'TenantComparison' => [App\Filament\Central\Pages\TenantComparison::class],
]);

test('central page can render', function (string $pageClass) {
    Livewire::test($pageClass)
        ->assertOk();
})->with('centralPages');
