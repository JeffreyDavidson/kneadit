<?php

use App\Models\Engagement\PageView;
use App\Services\Analytics\PageViewTracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('detectPage returns correct page type for storefront.menu', function () {
    $tracker = new PageViewTracker;

    expect($tracker->detectPage('storefront.menu', '/menu'))->toBe('menu');
});

test('detectPage returns correct page type for storefront.home', function () {
    $tracker = new PageViewTracker;

    expect($tracker->detectPage('storefront.home', '/'))->toBe('home');
});

test('detectPage returns correct page type for storefront.about', function () {
    $tracker = new PageViewTracker;

    expect($tracker->detectPage('storefront.about', '/about'))->toBe('about');
});

test('detectPage returns correct page type for storefront.reviews', function () {
    $tracker = new PageViewTracker;

    expect($tracker->detectPage('storefront.reviews', '/reviews'))->toBe('reviews');
});

test('detectPage returns correct page type for order.create', function () {
    $tracker = new PageViewTracker;

    expect($tracker->detectPage('order.create', '/order'))->toBe('order');
});

test('detectPage returns correct page type for contact.show', function () {
    $tracker = new PageViewTracker;

    expect($tracker->detectPage('contact.show', '/contact'))->toBe('contact');
});

test('detectPage returns home for empty path', function () {
    $tracker = new PageViewTracker;

    expect($tracker->detectPage(null, '/'))->toBe('home');
});

test('detectPage returns null for unknown routes', function () {
    $tracker = new PageViewTracker;

    expect($tracker->detectPage('admin.dashboard', '/admin/dashboard'))->toBeNull();
});

test('detectPage returns null for unknown route with non-empty path', function () {
    $tracker = new PageViewTracker;

    expect($tracker->detectPage(null, '/some/random/path'))->toBeNull();
});

test('track creates a PageView record for known pages', function () {
    $tracker = new PageViewTracker;

    $request = Request::create('/menu', 'GET');
    $request->setLaravelSession(app('session.store'));
    $request->setRouteResolver(function () {
        $route = new Illuminate\Routing\Route('GET', '/menu', []);
        $route->name('storefront.menu');

        return $route;
    });

    $tracker->track($request);

    expect(PageView::count())->toBeGreaterThanOrEqual(1);
    expect(PageView::where('page', 'menu')->exists())->toBeTrue();
});

test('track does not create record for unknown pages', function () {
    $tracker = new PageViewTracker;

    $request = Request::create('/admin/dashboard', 'GET');
    $request->setLaravelSession(app('session.store'));
    $request->setRouteResolver(function () {
        $route = new Illuminate\Routing\Route('GET', '/admin/dashboard', []);
        $route->name('admin.dashboard');

        return $route;
    });

    $tracker->track($request);

    expect(PageView::count())->toBe(0);
});

test('track throttles duplicate views within 60 minutes', function () {
    $tracker = new PageViewTracker;

    $session = app('session.store');

    $request = Request::create('/menu', 'GET');
    $request->setLaravelSession($session);
    $request->setRouteResolver(function () {
        $route = new Illuminate\Routing\Route('GET', '/menu', []);
        $route->name('storefront.menu');

        return $route;
    });

    $tracker->track($request);
    $firstCount = PageView::where('page', 'menu')->whereNull('product_id')->count();

    $tracker->track($request);
    $secondCount = PageView::where('page', 'menu')->whereNull('product_id')->count();

    expect($secondCount)->toBe($firstCount);
});
