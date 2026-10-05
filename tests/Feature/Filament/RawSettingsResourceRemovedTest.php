<?php

use App\Models\Platform\Setting;
use Filament\Facades\Filament;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;

beforeEach(fn () => setUpCentralTest());

test('the bakery admin panel has no raw key/value settings resource', function () {
    $models = collect(Filament::getPanel('admin')->getResources())
        ->map(fn (string $resource): ?string => $resource::getModel());

    expect($models)->not->toContain(Setting::class)
        ->and(Route::has('filament.admin.resources.settings.index'))->toBeFalse();
});

test('no admin route serves the raw settings path', function () {
    $paths = collect(Route::getRoutes()->getRoutes())
        ->map(fn (RoutingRoute $route): string => $route->uri());

    expect($paths)->not->toContain('admin/settings');
});
