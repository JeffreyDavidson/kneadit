<?php

use App\Http\Middleware\EnsureOnboardingComplete;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('passes through when no tenant is initialized', function () {
    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/admin');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through for unauthenticated users', function () {
    // Bind a fake tenant so tenant() returns truthy
    app()->instance(Stancl\Tenancy\Contracts\Tenant::class, Mockery::mock(Stancl\Tenancy\Contracts\Tenant::class)->shouldIgnoreMissing());

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/admin');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});
