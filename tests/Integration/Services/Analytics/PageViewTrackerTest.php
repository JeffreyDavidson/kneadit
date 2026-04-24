<?php

use App\Models\Engagement\PageView;
use App\Services\Analytics\PageViewTracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('detectPage returns correct page type for storefront.menu', function () {
    $tracker = resolve(PageViewTracker::class);

    expect($tracker->detectPage('storefront.menu', '/menu'))->toBe('menu');
});

test('detectPage returns correct page type for storefront.home', function () {
    $tracker = resolve(PageViewTracker::class);

    expect($tracker->detectPage('storefront.home', '/'))->toBe('home');
});

test('detectPage returns correct page type for storefront.about', function () {
    $tracker = resolve(PageViewTracker::class);

    expect($tracker->detectPage('storefront.about', '/about'))->toBe('about');
});

test('detectPage returns correct page type for storefront.reviews', function () {
    $tracker = resolve(PageViewTracker::class);

    expect($tracker->detectPage('storefront.reviews', '/reviews'))->toBe('reviews');
});

test('detectPage returns correct page type for order.create', function () {
    $tracker = resolve(PageViewTracker::class);

    expect($tracker->detectPage('order.create', '/order'))->toBe('order');
});

test('detectPage returns correct page type for contact.show', function () {
    $tracker = resolve(PageViewTracker::class);

    expect($tracker->detectPage('contact.show', '/contact'))->toBe('contact');
});

test('detectPage returns home for empty path', function () {
    $tracker = resolve(PageViewTracker::class);

    expect($tracker->detectPage(null, '/'))->toBe('home');
});

test('detectPage returns null for unknown routes', function () {
    $tracker = resolve(PageViewTracker::class);

    expect($tracker->detectPage('admin.dashboard', '/admin/dashboard'))->toBeNull();
});

test('detectPage returns null for unknown route with non-empty path', function () {
    $tracker = resolve(PageViewTracker::class);

    expect($tracker->detectPage(null, '/some/random/path'))->toBeNull();
});

test('track creates a PageView record for known pages', function () {
    $tracker = resolve(PageViewTracker::class);

    $request = Request::create('/menu', 'GET');
    $request->setLaravelSession(resolve('session.store'));
    $request->setRouteResolver(function () {
        $route = new Illuminate\Routing\Route('GET', '/menu', []);
        $route->name('storefront.menu');

        return $route;
    });

    $tracker->track($request);

    expect(PageView::query()->count())->toBeGreaterThanOrEqual(1)
        ->and(PageView::query()->where('page', 'menu')->exists())->toBeTrue();
});

test('track does not create record for unknown pages', function () {
    $tracker = resolve(PageViewTracker::class);

    $request = Request::create('/admin/dashboard', 'GET');
    $request->setLaravelSession(resolve('session.store'));
    $request->setRouteResolver(function () {
        $route = new Illuminate\Routing\Route('GET', '/admin/dashboard', []);
        $route->name('admin.dashboard');

        return $route;
    });

    $tracker->track($request);

    expect(PageView::query()->count())->toBe(0);
});

test('track throttles duplicate views within 60 minutes', function () {
    $tracker = resolve(PageViewTracker::class);

    $session = resolve('session.store');

    $request = Request::create('/menu', 'GET');
    $request->setLaravelSession($session);
    $request->setRouteResolver(function () {
        $route = new Illuminate\Routing\Route('GET', '/menu', []);
        $route->name('storefront.menu');

        return $route;
    });

    $tracker->track($request);
    $firstCount = PageView::query()->where('page', 'menu')->whereNull('product_id')->count();

    $tracker->track($request);
    $secondCount = PageView::query()->where('page', 'menu')->whereNull('product_id')->count();

    expect($secondCount)->toBe($firstCount);
});
