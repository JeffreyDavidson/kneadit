<?php

use App\Http\Middleware\TrackPageView;
use App\Services\Analytics\PageViewTracker;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JMac\Testing\Double;

test('tracks GET requests', function () {
    $tracker = Double::for(PageViewTracker::class);
    $tracker->expects('track');

    $middleware = new TrackPageView($tracker);
    $request = Request::create('/storefront', 'GET');

    $middleware->handle($request, fn () => new Response('OK'));
});

test('skips POST requests', function () {
    $tracker = Double::for(PageViewTracker::class);
    $tracker->expects('track')->never();

    $middleware = new TrackPageView($tracker);
    $request = Request::create('/storefront', 'POST');

    $middleware->handle($request, fn () => new Response('OK'));
});

test('skips ajax requests', function () {
    $tracker = Double::for(PageViewTracker::class);
    $tracker->expects('track')->never();

    $middleware = new TrackPageView($tracker);
    $request = Request::create('/storefront', 'GET', [], [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

    $middleware->handle($request, fn () => new Response('OK'));
});
