<?php

use App\Http\Controllers\Central\RootController;
use App\Http\Controllers\Storefront\HomeController;
use App\Models\Platform\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\get;

beforeEach(fn () => setUpCentralTest());

function renderRootFor(?Tenant $tenant = null): View|Response
{
    if ($tenant !== null) {
        app()->instance(Stancl\Tenancy\Contracts\Tenant::class, $tenant);
        app()->instance('currentTenant', $tenant);
    }

    return resolve(RootController::class)(resolve(HomeController::class));
}

test('central requests render the platform welcome page', function () {
    $response = renderRootFor();

    throw_unless($response instanceof View, RuntimeException::class, 'Expected the platform welcome view.');

    expect($response->name())->toBe('platform.welcome');
});

test('active tenant requests render the storefront home page', function () {
    $tenant = new Tenant([
        'id' => 'root-bakery',
        'storefront_enabled' => true,
    ]);

    $response = renderRootFor($tenant);

    throw_unless($response instanceof View, RuntimeException::class, 'Expected the storefront home view.');

    expect($response->name())->toBe('storefront.home');
});

test('the web middleware initializes tenant context before root dispatch', function () {
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
    $tenant = Tenant::factory()->create([
        'id' => 'rootroute',
        'storefront_enabled' => true,
    ]);
    DB::table('domains')->insert([
        'domain' => 'rootroute',
        'tenant_id' => $tenant->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    get('http://rootroute.kneadit.test/')
        ->assertOk()
        ->assertViewIs('storefront.home');
});

test('disabled tenant storefronts redirect only to valid external websites', function () {
    $tenant = new Tenant([
        'id' => 'external-bakery',
        'storefront_enabled' => false,
        'external_website' => 'https://bakery.example.com',
    ]);

    $response = renderRootFor($tenant);

    throw_unless($response instanceof RedirectResponse, RuntimeException::class, 'Expected an external redirect.');

    expect($response->headers->get('Location'))->toBe('https://bakery.example.com');
});

test('disabled tenant storefronts reject unsafe external redirects', function () {
    $tenant = new Tenant([
        'id' => 'disabled-bakery',
        'store_name' => 'Disabled Bakery',
        'storefront_enabled' => false,
        'external_website' => 'javascript:alert(1)',
    ]);

    $response = renderRootFor($tenant);

    throw_unless($response instanceof HttpResponse, RuntimeException::class, 'Expected the disabled storefront response.');

    $view = $response->getOriginalContent();
    throw_unless($view instanceof View, RuntimeException::class, 'Expected the disabled storefront view.');

    expect($response->getStatusCode())->toBe(200)
        ->and($view->name())->toBe('platform.storefront-disabled');
});
