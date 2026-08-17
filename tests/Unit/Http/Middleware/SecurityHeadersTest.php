<?php

use App\Http\Middleware\SecurityHeaders;
use App\Support\Csp\CspNonce;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

test('security headers middleware adds required headers', function () {
    $middleware = new SecurityHeaders(new CspNonce);
    $request = Request::create('/test');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->headers->get('X-Content-Type-Options'))->toBe('nosniff')
        ->and($response->headers->get('X-Frame-Options'))->toBe('SAMEORIGIN')
        ->and($response->headers->get('Referrer-Policy'))->toBe('strict-origin-when-cross-origin')
        ->and($response->headers->get('Permissions-Policy'))->toBe('camera=(), geolocation=(), microphone=()');
});

test('security headers middleware enforces CSP by default', function () {
    $middleware = new SecurityHeaders(new CspNonce);
    $request = Request::create('/test');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->headers->get('Content-Security-Policy'))
        ->toContain("default-src 'self'")
        ->toContain('https://js.stripe.com')
        ->toContain('https://fonts.gstatic.com')
        ->toContain('report-uri ')
        ->and($response->headers->get('Content-Security-Policy-Report-Only'))
        ->toBeNull();
});

test('security headers middleware supports an explicit report-only rollback', function () {
    config(['csp.mode' => 'report-only']);

    $middleware = new SecurityHeaders(new CspNonce);
    $request = Request::create('/test');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->headers->get('Content-Security-Policy-Report-Only'))
        ->toContain("default-src 'self'")
        ->toContain('report-uri ')
        ->and($response->headers->get('Content-Security-Policy'))
        ->toBeNull();
});

test('security headers middleware fails closed for an invalid CSP mode', function () {
    config(['csp.mode' => 'invalid']);

    $middleware = new SecurityHeaders(new CspNonce);
    $request = Request::create('/test');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->headers->get('Content-Security-Policy'))
        ->toContain("default-src 'self'")
        ->and($response->headers->get('Content-Security-Policy-Report-Only'))
        ->toBeNull();
});

test('CSP header includes a nonce token in script-src and style-src', function () {
    $nonce = new CspNonce;
    $middleware = new SecurityHeaders($nonce);

    $response = $middleware->handle(Request::create('/test'), fn () => new Response('OK'));

    $header = $response->headers->get('Content-Security-Policy');
    $token = $nonce->sourceList();

    expect($header)
        ->toContain("script-src 'self' {$token}")
        ->toContain("style-src 'self' {$token}")
        ->not->toContain("script-src 'self' {$token} 'unsafe-inline'")
        ->not->toContain("script-src-elem 'self' {$token} 'unsafe-inline'");
});
