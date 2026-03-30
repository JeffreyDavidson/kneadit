<?php

use App\Http\Middleware\EnsureStorefrontEnabled;
use Illuminate\Http\Request;

test('middleware passes when no tenant context', function () {
    $middleware = new EnsureStorefrontEnabled;
    $request = Request::create('/');

    $response = $middleware->handle($request, fn () => response('OK'));

    expect($response->getContent())->toBe('OK');
});
