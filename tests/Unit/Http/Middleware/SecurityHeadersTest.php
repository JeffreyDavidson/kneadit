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
        ->and($response->headers->get('Referrer-Policy'))->toBe('strict-origin-when-cross-origin');
});

test('security headers middleware emits CSP in Report-Only mode (no enforcement yet)', function () {
    $middleware = new SecurityHeaders(new CspNonce);
    $request = Request::create('/test');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->headers->get('Content-Security-Policy-Report-Only'))
        ->toContain("default-src 'self'")
        ->toContain('https://js.stripe.com')
        ->toContain('https://fonts.gstatic.com')
        ->toContain('report-uri ')
        ->and($response->headers->get('Content-Security-Policy'))
        ->toBeNull(); // Enforcement not enabled yet
});

test('CSP header includes a nonce token in script-src and style-src', function () {
    $nonce = new CspNonce;
    $middleware = new SecurityHeaders($nonce);

    $response = $middleware->handle(Request::create('/test'), fn () => new Response('OK'));

    $header = $response->headers->get('Content-Security-Policy-Report-Only');
    $token = $nonce->sourceList();

    expect($header)
        ->toContain("script-src 'self' {$token}")
        ->toContain("style-src 'self' {$token}");
});
