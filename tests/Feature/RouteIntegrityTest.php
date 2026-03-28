<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('all named routes can be generated without errors', function () {
    $routes = collect(app('router')->getRoutes()->getRoutesByName());
    $failures = [];

    foreach ($routes as $name => $route) {
        // Skip routes with required parameters
        if (preg_match('/\{[^?]/', $route->uri())) {
            continue;
        }

        // Skip livewire and webhook routes
        if (str_starts_with($name, 'livewire.') || str_contains($name, 'webhook')) {
            continue;
        }

        try {
            $url = route($name);
            expect($url)->toBeString();
        } catch (Throwable $e) {
            $failures[] = "{$name}: {$e->getMessage()}";
        }
    }

    expect($failures)->toBeEmpty(
        "Route generation failures:\n" . implode("\n", $failures),
    );
});
