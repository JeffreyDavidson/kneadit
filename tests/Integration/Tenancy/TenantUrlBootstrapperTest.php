<?php

declare(strict_types=1);

use App\Actions\Platform\AddCustomDomain;
use App\Enums\Orders\OrderStatus;
use App\Mail\Customers\AbandonedCartRecoveryMail;
use App\Mail\Customers\CustomerCampaignMail;
use App\Mail\Customers\ProductAvailableMail;
use App\Mail\Customers\ReviewRequestMail;
use App\Mail\Orders\NewOrderNotificationMail;
use App\Mail\Orders\OrderStatusMail;
use App\Models\Customers\Customer;
use App\Models\Engagement\CustomerCampaign;
use App\Models\Inventory\Product;
use App\Models\Orders\Cart;
use App\Models\Orders\Order;
use App\Models\Platform\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpCentralTest();

    config([
        'app.url' => 'https://app.example.test',
        'tenancy.tenant_domain' => 'example.test',
        'tenancy.central_domains' => ['localhost'],
    ]);

    test()->tenant = Tenant::factory()->create(['id' => 'sunrise']);
});

afterEach(function () {
    tenancy()->end();
});

dataset('background mail links', [
    'review request' => [
        fn () => new ReviewRequestMail(Order::factory()->for(Customer::factory())->create()),
        '/review/',
    ],
    'abandoned cart recovery' => [
        fn () => new AbandonedCartRecoveryMail(Cart::factory()->create(), Customer::factory()->create()),
        '/cart/recover/',
    ],
    'campaign tracking pixel' => [
        fn () => new CustomerCampaignMail(CustomerCampaign::factory()->create(), Customer::factory()->create(), str_repeat('A', 26)),
        '/track/email-open/',
    ],
    'product available' => [
        fn () => new ProductAvailableMail(Product::factory()->create(), 'Alice'),
        '/order',
    ],
    'order ready' => [
        fn () => new OrderStatusMail(Order::factory()->for(Customer::factory())->create(), OrderStatus::Ready),
        '/track',
    ],
    'new order notification' => [
        fn () => new NewOrderNotificationMail(Order::factory()->for(Customer::factory())->create()),
        '/admin',
    ],
]);

dataset('signed background mail links', [
    'review request' => [
        fn () => new ReviewRequestMail(Order::factory()->for(Customer::factory())->create()),
        '/review/',
    ],
    'abandoned cart recovery' => [
        fn () => new AbandonedCartRecoveryMail(Cart::factory()->create(), Customer::factory()->create()),
        '/cart/recover/',
    ],
]);

test('routes built with no request point at the bakery storefront', function () {
    tenancy()->initialize(test()->tenant);

    expect(route('storefront.submitReview', ['order' => 'ORD-1']))
        ->toStartWith('https://sunrise.example.test/review/ORD-1');
});

test('links in a background mail point at the bakery storefront', function (Closure $makeMail, string $path) {
    tenancy()->initialize(test()->tenant);

    $html = $makeMail()->render();

    expect($html)
        ->toContain("https://sunrise.example.test{$path}")
        ->not->toContain("http://localhost{$path}");
})->with('background mail links');

test('a signed link in a background mail validates on the bakery host', function (Closure $makeMail, string $path) {
    tenancy()->initialize(test()->tenant);
    preg_match('#https://[^"\s]*'.preg_quote($path, '#').'[^"\s]*?signature=[a-f0-9]{64}#', $makeMail()->render(), $matches);
    $url = html_entity_decode($matches[0] ?? '');

    $response = withoutMiddleware(tenantMiddleware())->get($url);

    expect($url)->toStartWith("https://sunrise.example.test{$path}")
        ->and($response->getStatusCode())->not->toBe(403);
})->with('signed background mail links');

test('ending tenancy restores the central root', function () {
    tenancy()->initialize(test()->tenant);
    tenancy()->end();

    expect(route('home'))->toBe('http://localhost');
});

test('a bakery with a verified custom domain links to that domain', function () {
    resolve(AddCustomDomain::class)(test()->tenant, 'sweetdreams.test');
    test()->tenant->update(['custom_domain_verified_at' => now()]);
    tenancy()->initialize(test()->tenant->refresh());

    expect(route('storefront.submitReview', ['order' => 'ORD-1']))
        ->toStartWith('https://sweetdreams.test/review/ORD-1');
});

test('a bakery with an unverified custom domain keeps linking to its subdomain', function () {
    resolve(AddCustomDomain::class)(test()->tenant, 'sweetdreams.test');
    tenancy()->initialize(test()->tenant->refresh());

    expect(route('storefront.submitReview', ['order' => 'ORD-1']))
        ->toStartWith('https://sunrise.example.test/review/ORD-1');
});

test('requests already on a bakery host keep that host', function () {
    URL::setRequest(Request::create('https://sunrise.example.test/menu'));

    tenancy()->initialize(test()->tenant);

    expect(route('storefront.submitReview', ['order' => 'ORD-1']))
        ->toStartWith('https://sunrise.example.test/review/ORD-1');
});

test('the powered-by link in a background mail still points at the platform', function () {
    tenancy()->initialize(test()->tenant);

    $html = new ProductAvailableMail(Product::factory()->create(), 'Alice')->render();

    expect($html)->toContain('Powered by <a href="https://app.example.test"');
});

test('requests on a custom domain keep that domain', function () {
    resolve(AddCustomDomain::class)(test()->tenant, 'sweetdreams.test');
    URL::setRequest(Request::create('https://sweetdreams.test/menu'));

    tenancy()->initialize(test()->tenant->refresh());

    expect(route('storefront.submitReview', ['order' => 'ORD-1']))
        ->toStartWith('https://sweetdreams.test/review/ORD-1');
});
