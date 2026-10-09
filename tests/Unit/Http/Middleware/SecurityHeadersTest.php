<?php

use App\Http\Middleware\SecurityHeaders;
use App\Support\Csp\CspNonce;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Vite;

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

test('security headers middleware can skip the CSP for the admin panels', function () {
    $middleware = new SecurityHeaders(new CspNonce);

    $response = $middleware->handle(
        Request::create('/test'),
        fn () => new Response('OK'),
        SecurityHeaders::WITHOUT_CSP,
    );

    expect($response->headers->get('X-Content-Type-Options'))->toBe('nosniff')
        ->and($response->headers->get('X-Frame-Options'))->toBe('SAMEORIGIN')
        ->and($response->headers->get('Referrer-Policy'))->toBe('strict-origin-when-cross-origin')
        ->and($response->headers->has('Content-Security-Policy'))->toBeFalse()
        ->and($response->headers->has('Content-Security-Policy-Report-Only'))->toBeFalse();
});

test('the nonce is handed to Vite before the response is built so Livewire tags carry it', function () {
    $nonce = new CspNonce;
    $middleware = new SecurityHeaders($nonce);

    $middleware->handle(Request::create('/test'), fn () => new Response('OK'));

    expect(Vite::cspNonce())->toBe($nonce->value());
});

test('CSP lets the address suggestions load Google Maps and call Places', function () {
    $middleware = new SecurityHeaders(new CspNonce);

    $response = $middleware->handle(Request::create('/test'), fn () => new Response('OK'));

    $directives = collect(explode('; ', (string) $response->headers->get('Content-Security-Policy')))
        ->mapWithKeys(function (string $directive): array {
            $parts = explode(' ', $directive);

            return [array_shift($parts) => $parts];
        });

    expect($directives['script-src'])->toContain('https://maps.googleapis.com', 'https://maps.gstatic.com')
        ->and($directives['script-src-elem'])->toContain('https://maps.googleapis.com', 'https://maps.gstatic.com')
        ->and($directives['connect-src'])->toContain('https://maps.googleapis.com', 'https://places.googleapis.com');
});

test('CSP allows no outside origins beyond the ones the storefront uses', function () {
    $middleware = new SecurityHeaders(new CspNonce);

    $response = $middleware->handle(Request::create('/test'), fn () => new Response('OK'));

    preg_match_all('#https://[a-z0-9.-]+#', (string) $response->headers->get('Content-Security-Policy'), $matches);
    $origins = collect($matches[0])
        ->reject(fn (string $origin): bool => str_starts_with(route('csp.report'), $origin))
        ->unique()
        ->sort()
        ->values()
        ->all();

    expect($origins)->toBe([
        'https://api.stripe.com',
        'https://cdn.jsdelivr.net',
        'https://cdn.usefathom.com',
        'https://checkout.stripe.com',
        'https://fonts.googleapis.com',
        'https://fonts.gstatic.com',
        'https://hooks.stripe.com',
        'https://js.stripe.com',
        'https://maps.googleapis.com',
        'https://maps.gstatic.com',
        'https://places.googleapis.com',
        'https://www.google.com',
    ]);
});
