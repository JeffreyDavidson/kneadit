<?php

use App\Filament\Central\Pages\Activity;
use App\Filament\Central\Pages\Analytics;
use App\Filament\Central\Pages\BakeryInsights;
use App\Filament\Central\Pages\Dashboard;
use App\Filament\Central\Pages\DataExport;
use App\Filament\Central\Pages\FeatureUsage;
use App\Filament\Central\Pages\MaintenanceMode;
use App\Filament\Central\Pages\OnboardingTracker;
use App\Filament\Central\Pages\TenantComparison;
use App\Models\Staff\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

dataset('centralPageGroups', [
    'Insights pages' => [
        Dashboard::class,
        Analytics::class,
        BakeryInsights::class,
    ],
    'Operations pages' => [
        Activity::class,
        DataExport::class,
        MaintenanceMode::class,
    ],
    'Platform pages' => [
        FeatureUsage::class,
        OnboardingTracker::class,
        TenantComparison::class,
    ],
]);

test('central pages can render', function (string ...$pageClasses) {
    foreach ($pageClasses as $pageClass) {
        livewire($pageClass)
            ->assertOk();
    }
})->with('centralPageGroups');
