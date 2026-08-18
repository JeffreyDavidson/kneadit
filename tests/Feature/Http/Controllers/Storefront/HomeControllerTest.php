<?php

use App\Http\Controllers\Storefront\HomeController;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();

    Route::get('/storefront-home-test', HomeController::class);
});

test('storefront home page renders', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/storefront-home-test');

    $response->assertOk();
});

test('biscotto storefront theme renders its branded navigation and hero', function () {
    settings([
        'storefront_theme' => 'biscotto',
        'store_name' => 'Bakery on Biscotto',
        'hero_tagline' => 'Where Sourdough Dreams Come True',
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get('/storefront-home-test');

    $response->assertOk()
        ->assertSee('biscotto-nav', false)
        ->assertSee('biscotto-hero', false)
        ->assertSee('Where Sourdough Dreams Come True');
});
