<?php

use App\Filament\Shared\Dashboard\WidgetMeta;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
    Feature::define('growth-features', fn () => true);
});

dataset('widgetsAtAllSizes', function (): array {
    $cases = [];
    foreach (array_keys(WidgetMeta::all()) as $key) {
        $class = WidgetMeta::classFor($key);
        if ($class === null) {
            throw new LogicException("Missing widget class for {$key}.");
        }

        foreach (WidgetMeta::allowedSizesFor($key) as $size) {
            $cases["{$key} @ {$size->value}"] = [$class, $size->value];
        }
    }

    return $cases;
});

test('widget renders cleanly at each allowed size', function (string $class, string $size) {
    // dashboardSize gets ignored by widgets that don't use the HasDashboardSize trait,
    // so passing it unconditionally is safe.
    Livewire::test($class, ['dashboardSize' => $size])->assertOk();
})->with('widgetsAtAllSizes');
