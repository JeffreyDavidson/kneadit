<?php

use App\Http\Middleware\EnsureSubscribed;
use App\Models\Staff\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

beforeEach(fn () => setUpCentralTest());

test('allows subscribed users through', function () {
    $user = User::factory()->owner()->create();
    $user->subscription = Mockery::mock()
        ->shouldReceive('valid')->andReturn(true)
        ->getMock();

    $request = Request::create('/admin');
    $request->setUserResolver(fn () => $user);

    // Mock user subscribed method
    $user = Mockery::mock($user)->makePartial();
    $user->shouldReceive('subscribed')->with('default')->andReturn(true);

    $request->setUserResolver(fn () => $user);

    $middleware = new EnsureSubscribed;
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('allows trial users through', function () {
    $user = Mockery::mock(User::factory()->owner()->create())->makePartial();
    $user->shouldReceive('subscribed')->with('default')->andReturn(false);
    $user->shouldReceive('onTrial')->andReturn(true);

    $request = Request::create('/admin');
    $request->setUserResolver(fn () => $user);

    $middleware = new EnsureSubscribed;
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('redirects unsubscribed non-trial users to billing', function () {
    $user = Mockery::mock(User::factory()->owner()->create())->makePartial();
    $user->shouldReceive('subscribed')->with('default')->andReturn(false);
    $user->shouldReceive('onTrial')->andReturn(false);

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
    $user = Mockery::mock(User::factory()->owner()->create())->makePartial();
    $user->shouldReceive('subscribed')->with('default')->andReturn(true);

    Gate::define('has-plan', fn () => false);

    $request = Request::create('/admin');
    $request->setUserResolver(fn () => $user);

    $middleware = new EnsureSubscribed;

    expect(fn () => $middleware->handle($request, fn () => new Response('OK'), 'pro'))
        ->toThrow(Symfony\Component\HttpKernel\Exception\HttpException::class);
});
