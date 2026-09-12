<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('about controller passes settings and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.about', [], false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content')
        ->assertViewHas('storefrontTheme');
});

test('biscotto theme renders its story and portrait presentation', function () {
    settings(['storefront_theme' => 'biscotto']);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.about', [], false));

    $response->assertOk()
        ->assertSee('biscotto-about-hero', false)
        ->assertSee('biscotto-about-photo', false)
        ->assertSee('With love and flour dust');
});
