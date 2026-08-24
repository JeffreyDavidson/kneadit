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

test('marketing home page renders its navigation and hero', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/storefront-home-test');

    $response->assertOk()
        ->assertSee('nav-link', false)
        ->assertSee('hero-fade-1', false);
});
