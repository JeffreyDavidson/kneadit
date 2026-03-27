<?php

use App\Http\Middleware\InitializeTenancyIfNeeded;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

test('redirects www to apex domain', function () {
    $middleware = new InitializeTenancyIfNeeded;
    $request = Request::create('https://www.getkneadit.app/pricing');
    $request->headers->set('HOST', 'www.getkneadit.app');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getStatusCode())->toBe(301)
        ->and($response->headers->get('Location'))->toContain('getkneadit.app/pricing')
        ->and($response->headers->get('Location'))->not->toContain('www.');
});

test('passes through for central domains', function () {
    config(['tenancy.central_domains' => ['getkneadit.app']]);

    $middleware = new InitializeTenancyIfNeeded;
    $request = Request::create('https://getkneadit.app/');
    $request->headers->set('HOST', 'getkneadit.app');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});
