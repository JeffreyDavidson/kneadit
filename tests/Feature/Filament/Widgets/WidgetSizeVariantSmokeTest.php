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

dataset('widgetsAtAllSizes', function (): array {
    $cases = [];
    foreach (WidgetMeta::all() as $key => $meta) {
        foreach (WidgetMeta::allowedSizesFor($key) as $size) {
            $cases["{$key} @ {$size->value}"] = [$meta->class, $size->value];
        }
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
