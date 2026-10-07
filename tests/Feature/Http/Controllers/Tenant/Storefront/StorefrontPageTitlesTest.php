<?php

use App\Models\Content\TenantBlogPost;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
    app()->instance(TenantSettings::class, makeTenantSettings(store: makeStoreInfo(['name' => 'Sunrise Bakery'])));
});

function pageHtml(string $url): string
{
    return withoutMiddleware(tenantMiddleware())->get($url)->assertOk()->getContent();
}

test('each storefront page has its own title and description', function (string $routeName, string $title) {
    $html = pageHtml(route($routeName, [], false));

    expect($html)->toContain("<title>{$title} · Sunrise Bakery</title>")
        ->and($html)->toContain("<meta property=\"og:title\" content=\"{$title} · Sunrise Bakery\" />")
        ->and($html)->not->toContain('<meta name="description" content="" />');
})->with([
    'menu' => ['storefront.menu', 'Menu'],
    'order' => ['order.create', 'Order'],
    'track' => ['order.track', 'Track Your Order'],
    'gift cards' => ['storefront.giftCards', 'Gift Cards'],
    'gallery' => ['storefront.gallery', 'Gallery'],
    'reviews' => ['storefront.reviews', 'Reviews'],
    'about' => ['storefront.about', 'About'],
    'contact' => ['contact.show', 'Contact'],
    'catering' => ['storefront.catering', 'Catering'],
    'blog' => ['storefront.blog', 'Blog'],
]);

test('storefront pages have distinct titles', function () {
    $titles = collect(['storefront.menu', 'order.create', 'storefront.gallery', 'storefront.about'])
        ->map(fn (string $name): string => preg_match('#<title>(.*?)</title>#', pageHtml(route($name, [], false)), $m) ? $m[1] : '');

    expect($titles->unique())->toHaveCount(4);
});

test('the order confirmation page is titled for the order', function () {
    $order = Order::factory()->create();

    $html = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->get(route('order.confirmation', $order, false))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('<title>Order Confirmation · Sunrise Bakery</title>');
});

test('a blog post is titled with its own title and described by its excerpt', function () {
    $post = TenantBlogPost::factory()->published()->create([
        'title' => 'Sourdough Starter Care',
        'excerpt' => 'How to keep your starter happy.',
    ]);

    $html = pageHtml(route('storefront.blog.show', $post, false));

    expect($html)->toContain('<title>Sourdough Starter Care · Sunrise Bakery</title>')
        ->and($html)->toContain('<meta name="description" content="How to keep your starter happy." />');
});
