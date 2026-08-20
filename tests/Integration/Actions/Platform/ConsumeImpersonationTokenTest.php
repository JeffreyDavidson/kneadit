<?php

use App\Actions\Platform\ConsumeImpersonationToken;
use App\Enums\Staff\UserRole;
use App\Models\Platform\ImpersonationToken;
use App\Models\Staff\User;

beforeEach(fn () => setUpCentralTest());

test('consumes valid token and returns owner user', function () {
    $rawToken = 'test-token-abc123';

    ImpersonationToken::factory()->create([
        'token' => hash('sha256', $rawToken),
        'tenant_id' => 'test',
        'expires_at' => now()->addMinutes(5),
    ]);

    User::factory()->owner()->create();

    $user = resolve(ConsumeImpersonationToken::class)($rawToken, '10.0.0.1');

    $record = ImpersonationToken::query()->where('token', hash('sha256', $rawToken))->firstOrFail();

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

    resolve(ConsumeImpersonationToken::class)($rawToken);
})->throws(Symfony\Component\HttpKernel\Exception\HttpException::class);

test('aborts on expired token', function () {
    ImpersonationToken::factory()->create([
        'token' => hash('sha256', 'expired-token'),
        'tenant_id' => 'test',
        'expires_at' => now()->subMinutes(5),
    ]);

    resolve(ConsumeImpersonationToken::class)('expired-token');
})->throws(Symfony\Component\HttpKernel\Exception\HttpException::class);
