<?php

use App\Http\Middleware\EnsureSubscribed;
use App\Models\Staff\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Mockery\MockInterface;

beforeEach(fn () => setUpCentralTest());

function subscribedUserMock(): User&MockInterface
{
    $user = User::factory()->owner()->create();
    $mock = Mockery::mock($user)->makePartial();

    if (! $mock instanceof User || ! $mock instanceof MockInterface) {
        throw new RuntimeException('Mockery did not create a User instance.');
    }

    return $mock;
}

test('allows subscribed users through', function () {
    $user = subscribedUserMock();
    $user->allows(['subscribed' => true]);

    $request = Request::create('/admin');
    $request->setUserResolver(fn () => $user);

    $middleware = new EnsureSubscribed;
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('allows trial users through', function () {
    $user = subscribedUserMock();
    $user->allows([
        'subscribed' => false,
        'onTrial' => true,
    ]);

    $request = Request::create('/admin');
    $request->setUserResolver(fn () => $user);

    $middleware = new EnsureSubscribed;
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('redirects unsubscribed non-trial users to billing', function () {
    $user = subscribedUserMock();
    $user->allows([
        'subscribed' => false,
        'onTrial' => false,
    ]);

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
    $user = subscribedUserMock();
    $user->allows(['subscribed' => true]);

    Gate::define('has-plan', fn () => false);

    $request = Request::create('/admin');
    $request->setUserResolver(fn () => $user);

    $middleware = new EnsureSubscribed;

    expect(fn () => $middleware->handle($request, fn () => new Response('OK'), 'pro'))
        ->toThrow(Symfony\Component\HttpKernel\Exception\HttpException::class);
});
