<?php

use App\Http\Middleware\CheckPlatformMaintenance;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

beforeEach(fn () => setUpCentralTest());

test('passes through when maintenance mode is off', function () {
    $middleware = new CheckPlatformMaintenance;
    $request = Request::create('/storefront');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('returns 503 for affected service when maintenance mode is on', function () {
    platformSettings([
        'maintenance_mode' => '1',
        'affected_services' => json_encode(['storefront']),
        'maintenance_message' => 'Down for maintenance',
    ]);

    $middleware = new CheckPlatformMaintenance;
    $request = Request::create('/products');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getStatusCode())->toBe(503);
});

test('passes through for unaffected service during maintenance', function () {
    platformSettings([
        'maintenance_mode' => '1',
        'affected_services' => json_encode(['api']),
        'maintenance_message' => 'API maintenance',
    ]);

    $middleware = new CheckPlatformMaintenance;
    $request = Request::create('/products');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('detects api service for api routes', function () {
    platformSettings([
        'maintenance_mode' => '1',
        'affected_services' => json_encode(['api']),
        'maintenance_message' => 'API down',
    ]);

    $middleware = new CheckPlatformMaintenance;
    $request = Request::create('/api/orders');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getStatusCode())->toBe(503);
});

test('detects admin service for admin routes', function () {
    platformSettings([
        'maintenance_mode' => '1',
        'affected_services' => json_encode(['admin']),
        'maintenance_message' => 'Admin down',
    ]);

    $middleware = new CheckPlatformMaintenance;
    $request = Request::create('/admin/dashboard');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getStatusCode())->toBe(503);
});
