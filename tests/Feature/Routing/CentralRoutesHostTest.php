<?php

use App\Models\Content\BlogPost;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route as RouteFacade;

use function Pest\Laravel\call;
use function Pest\Laravel\get;

/**
 * Routes served from routes/central/* and routes/billing.php that a bakery host
 * must keep answering. Every other central route has to answer 404 there.
 *
 * - home: RootController serves the bakery's storefront home on its own host.
 * - login: the sign-in redirect that the auth middleware sends signed-out visitors to; it
 *   only redirects to "/", which is the bakery's own home.
 * - logout: ends the session on the current host; it holds no data and only redirects to "/".
 */
const BAKERY_HOST_CENTRAL_ROUTE_ALLOWLIST = ['home', 'login', 'logout'];

/**
 * Central routes that are not handled by a controller in the Central namespace
 * (route views, redirects and closures), so they cannot be found by namespace.
 */
const CENTRAL_ROUTE_NAMES_WITHOUT_CONTROLLER = [
    'login',
    'pricing',
    'terms',
    'privacy',
    'robots',
    'verification.notice',
];

/**
 * @return Collection<int, Route>
 */
function centralRoutes(): Collection
{
    return collect(RouteFacade::getRoutes()->getRoutes())
        ->filter(function (Route $route): bool {
            // Bakery routes (some are served by Central controllers) run behind tenancy
            // middleware; domain-bound routes belong to the Filament panels.
            if ($route->getDomain() !== null || str_contains(implode(',', $route->gatherMiddleware()), 'InitializeTenancy')) {
                return false;
            }

            $controller = (string) $route->getControllerClass();

            return in_array($route->getName(), CENTRAL_ROUTE_NAMES_WITHOUT_CONTROLLER, true)
                || str_starts_with($controller, 'App\\Http\\Controllers\\Central\\')
                || str_starts_with($controller, 'App\\Http\\Controllers\\Billing\\');
        })
        ->values();
}

function bakeryHostUrl(Route $route): string
{
    $path = preg_replace('/\{[^}]+\}/', 'x', $route->uri());

    return "http://sunrise.kneadit.test/{$path}";
}

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);

    $owner = User::factory()->owner()->create();
    test()->tenant = Tenant::factory()->create(['id' => 'sunrise', 'user_id' => $owner->id]);
    test()->tenant->createDomain(['domain' => 'sunrise']);

    // Gives the {centralPost} binding something to resolve, so a 404 comes from the host check.
    BlogPost::factory()->published()->create(['slug' => 'x']);
});

test('the walk finds the central routes', function () {
    $names = centralRoutes()->map(fn (Route $route): ?string => $route->getName());

    expect(centralRoutes()->count())->toBeGreaterThan(30)
        ->and($names)->toContain('pricing', 'central.export', 'tenant.impersonate', 'billing.plans', 'register', 'robots');
});

test('every central-only route answers 404 on a bakery host', function () {
    $answered = [];

    foreach (centralRoutes() as $route) {
        if (in_array($route->getName(), BAKERY_HOST_CENTRAL_ROUTE_ALLOWLIST, true)) {
            continue;
        }

        $method = collect($route->methods())->first(fn (string $method): bool => $method !== 'HEAD');
        $status = call($method, bakeryHostUrl($route))->getStatusCode();

        if ($status !== 404) {
            $answered[] = "{$method} /{$route->uri()} ({$route->getName()}) answered {$status}";
        }
    }

    expect($answered)->toBeEmpty("Central routes answering on a bakery host:\n".implode("\n", $answered));
});

test('the allowlisted central routes still answer on a bakery host', function (string $name, string $method) {
    $route = centralRoutes()->first(fn (Route $route): bool => $route->getName() === $name);

    $status = call($method, bakeryHostUrl($route))->getStatusCode();

    expect($status)->not->toBe(404);
})->with([
    'home' => ['home', 'GET'],
    'login' => ['login', 'GET'],
    'logout' => ['logout', 'POST'],
]);

test('the public central pages still answer on the central host', function (string $path) {
    get("http://kneadit.test/{$path}")->assertOk();
})->with([
    'pricing' => ['pricing'],
    'terms' => ['terms'],
    'privacy' => ['privacy'],
    'changelog' => ['changelog'],
    'resources' => ['resources'],
    'directory' => ['directory'],
    'sitemap' => ['sitemap.xml'],
    'robots' => ['robots.txt'],
]);
