<?php

use App\Http\Middleware\SetActorContext;
use App\Models\Staff\User;
use App\Services\Audit\ActorContext;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    setUpCentralTest();
});

afterEach(function () {
    ActorContext::clear();
});

test('middleware records the authenticated user in ActorContext', function () {
    $user = User::factory()->create();
    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);

    (new SetActorContext)->handle($request, fn () => new Response('ok'));

    expect(ActorContext::id())->toBe($user->id);
});

test('middleware clears ActorContext when no user is present', function () {
    ActorContext::set(User::factory()->create());

    $request = Request::create('/');
    $request->setUserResolver(fn () => null);

    (new SetActorContext)->handle($request, fn () => new Response('ok'));

    expect(ActorContext::id())->toBeNull();
});

test('middleware does not blow up when sentry is bound but DSN is empty', function () {
    expect(app()->bound('sentry'))->toBeTrue()
        ->and(config('sentry.dsn'))->toBeNull();

    $user = User::factory()->create();
    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);

    $response = (new SetActorContext)->handle($request, fn () => new Response('ok'));

    expect($response->getContent())->toBe('ok');
});
