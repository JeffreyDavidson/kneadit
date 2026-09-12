<?php

use App\Http\Middleware\InitializeTenancyIfNeeded;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JMac\Testing\Double;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;

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

test('returns 503 when central row exists but tenant SQLite file is missing', function () {
    config(['tenancy.central_domains' => ['getkneadit.app']]);

    $stancl = new class extends Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain {
        public function handle(mixed $request, Closure $next): never
        {
            throw new Stancl\Tenancy\Exceptions\TenantDatabaseDoesNotExistException('tenantfoo');
        }
    };
    app()->instance(Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain::class, $stancl);

    $middleware = new InitializeTenancyIfNeeded;
    $request = Request::create('https://foo.getkneadit.app/admin');
    $request->headers->set('HOST', 'foo.getkneadit.app');

    expect(fn () => $middleware->handle($request, fn () => new Response('OK')))
        ->toThrow(Symfony\Component\HttpKernel\Exception\HttpException::class, 'Bakery temporarily unavailable. Run `php artisan tenants:doctor --fix` to repair.');
});
