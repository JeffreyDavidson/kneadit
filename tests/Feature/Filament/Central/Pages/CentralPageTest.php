<?php

use App\Models\Staff\User;
use Filament\Facades\Filament;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

dataset('centralPageGroups', [
    'Insights pages' => [
        App\Filament\Central\Pages\Dashboard::class,
        App\Filament\Central\Pages\Analytics::class,
        App\Filament\Central\Pages\BakeryInsights::class,
    ],
    'Operations pages' => [
        App\Filament\Central\Pages\Activity::class,
        App\Filament\Central\Pages\DataExport::class,
        App\Filament\Central\Pages\MaintenanceMode::class,
    ],
    'Platform pages' => [
        App\Filament\Central\Pages\FeatureUsage::class,
        App\Filament\Central\Pages\OnboardingTracker::class,
        App\Filament\Central\Pages\TenantComparison::class,
    ],
]);

test('central pages can render', function (string ...$pageClasses) {
    foreach ($pageClasses as $pageClass) {
        livewire($pageClass)
            ->assertOk();
    }
})->with('centralPageGroups');
