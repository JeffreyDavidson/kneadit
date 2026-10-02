<?php

use App\Actions\Platform\ConsumeImpersonationToken;
use App\Enums\Staff\UserRole;
use App\Models\Platform\ImpersonationToken;
use App\Models\Staff\User;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(fn () => setUpCentralTest());

test('consumes valid token and returns owner user', function () {
    $rawToken = 'test-token-abc123';

    ImpersonationToken::factory()->create([
        'token' => hash('sha256', $rawToken),
        'tenant_id' => 'test',
        'expires_at' => now()->addMinutes(5),
    ]);

    User::factory()->owner()->create();

    $user = resolve(ConsumeImpersonationToken::class)($rawToken, 'test', '10.0.0.1');

    $record = ImpersonationToken::query()->where('token', hash('sha256', $rawToken))->first();

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->role)->toBe(UserRole::Owner)
        ->and($record)->not->toBeNull()
        ->and($record->consumed_at)->not->toBeNull()
        ->and($record->consumer_ip)->toBe('10.0.0.1');
});

test('aborts when token already consumed', function () {
    $rawToken = 'already-used-token';

    ImpersonationToken::factory()->create([
        'token' => hash('sha256', $rawToken),
        'tenant_id' => 'test',
        'expires_at' => now()->addMinutes(5),
        'consumed_at' => now()->subMinute(),
    ]);

    resolve(ConsumeImpersonationToken::class)($rawToken, 'test');
})->throws(HttpException::class);

test('aborts on expired token', function () {
    ImpersonationToken::factory()->create([
        'token' => hash('sha256', 'expired-token'),
        'tenant_id' => 'test',
        'expires_at' => now()->subMinutes(5),
    ]);

    resolve(ConsumeImpersonationToken::class)('expired-token', 'test');
})->throws(HttpException::class);

test('aborts when the token was issued for a different bakery and leaves it unconsumed', function () {
    $rawToken = 'token-for-bakery-a';

    ImpersonationToken::factory()->create([
        'token' => hash('sha256', $rawToken),
        'tenant_id' => 'bakery-a',
        'expires_at' => now()->addMinutes(5),
    ]);

    User::factory()->owner()->create();

    expect(fn () => resolve(ConsumeImpersonationToken::class)($rawToken, 'bakery-b', '10.0.0.1'))
        ->toThrow(HttpException::class);

    $record = ImpersonationToken::query()->where('token', hash('sha256', $rawToken))->first();

    expect($record->consumed_at)->toBeNull()
        ->and($record->consumer_ip)->toBeNull()
        ->and(resolve(ConsumeImpersonationToken::class)($rawToken, 'bakery-a', '10.0.0.1'))
        ->toBeInstanceOf(User::class);
});

test('aborts when no bakery is initialized', function () {
    ImpersonationToken::factory()->create([
        'token' => hash('sha256', 'orphan-token'),
        'tenant_id' => 'test',
        'expires_at' => now()->addMinutes(5),
    ]);

    resolve(ConsumeImpersonationToken::class)('orphan-token', null);
})->throws(HttpException::class);

test('a token can be consumed only once', function () {
    $rawToken = 'single-use-token';

    ImpersonationToken::factory()->create([
        'token' => hash('sha256', $rawToken),
        'tenant_id' => 'test',
        'expires_at' => now()->addMinutes(5),
    ]);

    User::factory()->owner()->create();

    $action = resolve(ConsumeImpersonationToken::class);
    $first = $action($rawToken, 'test', '10.0.0.1');

    expect($first)->toBeInstanceOf(User::class)
        ->and(fn () => $action($rawToken, 'test', '10.0.0.2'))->toThrow(HttpException::class);

    $record = ImpersonationToken::query()->where('token', hash('sha256', $rawToken))->first();

    expect($record->consumer_ip)->toBe('10.0.0.1');
});
