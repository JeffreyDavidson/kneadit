<?php

use App\Models\Staff\User;
use App\Services\Audit\ActorContext;
use Illuminate\Support\Facades\Context;

beforeEach(fn () => ActorContext::clear());

test('id() returns null when context is empty', function () {
    expect(ActorContext::id())->toBeNull();
});

test('name() returns "System" when context is empty', function () {
    expect(ActorContext::name())->toBe('System');
});

test('set() with a User populates id and name in context', function () {
    $user = new User(['name' => 'Ada Lovelace']);
    $user->id = 42;

    ActorContext::set($user);

    expect(ActorContext::id())->toBe(42)
        ->and(ActorContext::name())->toBe('Ada Lovelace');
});

test('set(null) populates null id and falls back to System name', function () {
    ActorContext::set(null);

    expect(ActorContext::id())->toBeNull()
        ->and(ActorContext::name())->toBe('System');
});

test('clear() removes both id and name from context', function () {
    $user = new User(['name' => 'Linus']);
    $user->id = 1;
    ActorContext::set($user);

    ActorContext::clear();

    expect(Context::get('actor_id'))->toBeNull()
        ->and(Context::get('actor_name'))->toBeNull()
        ->and(ActorContext::id())->toBeNull()
        ->and(ActorContext::name())->toBe('System');
});

test('id() ignores non-int context values defensively', function () {
    Context::add('actor_id', 'not-an-int');

    expect(ActorContext::id())->toBeNull();
});

test('name() ignores empty-string context values', function () {
    Context::add('actor_name', '');

    expect(ActorContext::name())->toBe('System');
});
