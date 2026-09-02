<?php

use App\Http\Middleware\EnsureSubscribed;
use App\Models\Staff\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use JMac\Testing\Double;

beforeEach(fn () => setUpCentralTest());

test('allows subscribed users through', function () {
    $user = Double::for(User::factory()->owner()->create())->passthru();
    $user->expects('subscribed')->with('default')->returns(true);

    $request = Request::create('/admin');
    $request->setUserResolver(fn () => $user);

    $request->setUserResolver(fn () => $user);

    $middleware = new EnsureSubscribed;
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('allows trial users through', function () {
    $user = Double::for(User::factory()->owner()->create())->passthru();
    $user->expects('subscribed')->with('default')->returns(false);
    $user->expects('onTrial')->returns(true);

    $request = Request::create('/admin');
    $request->setUserResolver(fn () => $user);

    $middleware = new EnsureSubscribed;
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('redirects unsubscribed non-trial users to billing', function () {
    $user = Double::for(User::factory()->owner()->create())->passthru();
    $user->expects('subscribed')->with('default')->returns(false);
    $user->expects('onTrial')->returns(false);

    $request = Request::create('/admin');
    $request->setUserResolver(fn () => $user);

    $middleware = new EnsureSubscribed;
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getStatusCode())->toBe(302);
});

test('allows free-forever tenants through without a subscription', function () {
    $user = User::factory()->owner()->create();
    createTenant(['id' => 'free-forever-mw', 'user_id' => $user->id, 'free_forever' => true]);

    $request = Request::create('/admin');
    $request->setUserResolver(fn () => $user);

    $middleware = new EnsureSubscribed;
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('aborts with 403 for wrong plan when plan parameter specified', function () {
    $user = Double::for(User::factory()->owner()->create())->passthru();
    $user->expects('subscribed')->with('default')->returns(true);

    Gate::define('has-plan', fn () => false);

    $request = Request::create('/admin');
    $request->setUserResolver(fn () => $user);

    $middleware = new EnsureSubscribed;

    expect(fn () => $middleware->handle($request, fn () => new Response('OK'), 'pro'))
        ->toThrow(Symfony\Component\HttpKernel\Exception\HttpException::class);
});
