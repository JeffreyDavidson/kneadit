<?php

use App\Models\Platform\Tenant;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpCentralTest();

    // Persist the central tenant without provisioning a separate database for each test.
    test()->tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create());

    tenancy()->getBootstrappersUsing = fn (): array => [];
    tenancy()->initialize(test()->tenant);
});

test('a stored brand color that is not a hex color renders as the default in the storefront layout', function (string $stored) {
    test()->tenant->update(['brand_color_primary' => $stored]);

    withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.about', [], false))
        ->assertOk()
        ->assertSeeHtml('<meta name="theme-color" content="#d4920c" />')
        ->assertSeeHtml("fill='%23d4920c'")
        ->assertDontSeeHtml('display:none');
})->with([
    'css break-out' => 'red;}body{display:none',
    'named color' => 'red',
    'short hex' => '#fff',
]);

test('a valid brand color is used in the storefront layout', function () {
    test()->tenant->update(['brand_color_primary' => '#336699']);

    withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.about', [], false))
        ->assertOk()
        ->assertSeeHtml('<meta name="theme-color" content="#336699" />');
});
