<?php

use App\Filament\Shared\Dashboard\WidgetMeta;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
    Feature::define('growth-features', fn () => true);
});

dataset('widgetSizeGroups', function (): array {
    $groups = [];

    foreach (array_chunk(array_keys(WidgetMeta::all()), 5) as $index => $widgetKeys) {
        $groups['Widget group ' . ($index + 1)] = $widgetKeys;
    }

    return $groups;
});

test('widgets render cleanly at each allowed size', function (string ...$widgetKeys) {
    // dashboardSize gets ignored by widgets that don't use the HasDashboardSize trait,
    // so passing it unconditionally is safe.
    foreach ($widgetKeys as $widgetKey) {
        $widgetClass = WidgetMeta::classFor($widgetKey);

        if ($widgetClass === null) {
            throw new LogicException("Unknown widget key [{$widgetKey}].");
        }

        foreach (WidgetMeta::allowedSizesFor($widgetKey) as $size) {
            livewire($widgetClass, ['dashboardSize' => $size->value])
                ->assertOk();
        }
    }
})->with('widgetSizeGroups');
