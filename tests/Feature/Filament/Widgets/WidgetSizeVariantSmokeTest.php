<?php

use App\Enums\Filament\WidgetSize;
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

dataset('widgetsWithAllowedSizes', function (): array {
    $cases = [];
    foreach (WidgetMeta::all() as $key => $meta) {
        $sizes = array_map(
            fn (WidgetSize $size): string => $size->value,
            WidgetMeta::allowedSizesFor($key),
        );

        $cases[$key] = [$meta['class'], ...$sizes];
    }

    return $cases;
});

test('widget renders cleanly at each allowed size', function (string $class, string ...$sizes) {
    // dashboardSize gets ignored by widgets that don't use the HasDashboardSize trait,
    // so passing it unconditionally is safe.
    foreach ($sizes as $size) {
        Livewire::test($class, ['dashboardSize' => $size])->assertOk();
    }
})->with('widgetsWithAllowedSizes');
