<?php

use App\Http\Controllers\Storefront\HomeController;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();

    Route::get('/_tests/storefront-home', HomeController::class);
});

test('storefront home page renders', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/_tests/storefront-home');

    $response->assertOk();
});

test('biscotto storefront theme renders its branded navigation and hero', function () {
    settings([
        'storefront_theme' => 'biscotto',
        'store_name' => 'Bakery on Biscotto',
        'hero_tagline' => 'Where Sourdough Dreams Come True',
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get('/_tests/storefront-home');

    $response->assertOk()
        ->assertSee('biscotto-nav', false)
        ->assertSee('biscotto-hero', false)
        ->assertSee('Where Sourdough Dreams Come True');
});
