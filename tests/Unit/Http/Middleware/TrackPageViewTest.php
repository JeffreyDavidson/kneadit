<?php

use App\Http\Middleware\TrackPageView;
use App\Services\Analytics\PageViewTracker;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

test('tracks GET requests', function () {
    $tracker = Tests\Support\TypedMock::make(PageViewTracker::class);
    $tracker->expects('track');

    $middleware = new TrackPageView($tracker);
    $request = Request::create('/storefront', 'GET');

    $middleware->handle($request, fn () => new Response('OK'));
});

test('skips POST requests', function () {
    $tracker = Tests\Support\TypedMock::make(PageViewTracker::class);
    $tracker->shouldNotReceive('track');

    $middleware = new TrackPageView($tracker);
    $request = Request::create('/storefront', 'POST');

    $middleware->handle($request, fn () => new Response('OK'));
});

test('skips ajax requests', function () {
    $tracker = Tests\Support\TypedMock::make(PageViewTracker::class);
    $tracker->shouldNotReceive('track');

    $middleware = new TrackPageView($tracker);
    $request = Request::create('/storefront', 'GET', [], [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

    $middleware->handle($request, fn () => new Response('OK'));
});
