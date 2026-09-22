<?php

use App\Http\Middleware\InitializeTenancyIfNeeded;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JMac\Testing\Double;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;
use Stancl\Tenancy\Exceptions\TenantDatabaseDoesNotExistException;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('passes through when tenancy is already initialized', function () {
    $tenant = Double::for(TenantContract::class);
    app()->instance(TenantContract::class, $tenant);
    app()->bind('currentTenant', fn () => $tenant);

    $middleware = new InitializeTenancyIfNeeded;
    $request = Request::create('https://mybakery.getkneadit.app/');
    $request->headers->set('HOST', 'mybakery.getkneadit.app');

    $response = $middleware->handle($request, fn () => new Response('Already OK'));

    expect($response->getContent())->toBe('Already OK');
});

test('redirects the legacy application hostname to the current application domain', function () {
    $middleware = new InitializeTenancyIfNeeded;
    $request = Request::create('https://www.app.getkneadit.app/pricing');
    $request->headers->set('HOST', 'www.app.getkneadit.app');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getStatusCode())->toBe(301)
        ->and($response->headers->get('Location'))->toContain('app.getkneadit.app/pricing')
        ->and($response->headers->get('Location'))->not->toContain('www.');
});

test('does not redirect the standalone marketing domain to the application', function () {
    config(['tenancy.central_domains' => ['getkneadit.app']]);

    $middleware = new InitializeTenancyIfNeeded;
    $request = Request::create('https://getkneadit.app/');
    $request->headers->set('HOST', 'getkneadit.app');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through for central domains', function () {
    config(['tenancy.central_domains' => ['app.getkneadit.app']]);

    $middleware = new InitializeTenancyIfNeeded;
    $request = Request::create('https://app.getkneadit.app/');
    $request->headers->set('HOST', 'app.getkneadit.app');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through for the staging application domain', function () {
    $middleware = new InitializeTenancyIfNeeded;
    $request = Request::create('https://app.staging.getkneadit.app/');
    $request->headers->set('HOST', 'app.staging.getkneadit.app');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('returns 503 when central row exists but tenant SQLite file is missing', function () {
    config(['tenancy.central_domains' => ['getkneadit.app']]);

    $stancl = new class extends InitializeTenancyByDomainOrSubdomain
    {
        public function handle(mixed $request, Closure $next): never
        {
            throw new TenantDatabaseDoesNotExistException('tenantfoo');
        }
    };
    app()->instance(InitializeTenancyByDomainOrSubdomain::class, $stancl);

    $middleware = new InitializeTenancyIfNeeded;
    $request = Request::create('https://foo.getkneadit.app/admin');
    $request->headers->set('HOST', 'foo.getkneadit.app');

    expect(fn () => $middleware->handle($request, fn () => new Response('OK')))
        ->toThrow(HttpException::class, 'Bakery temporarily unavailable. Run `php artisan tenants:doctor --fix` to repair.');
});
