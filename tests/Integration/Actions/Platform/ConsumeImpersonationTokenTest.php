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

    $user = resolve(ConsumeImpersonationToken::class)($rawToken);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->role)->toBe(UserRole::Owner);

    expect(ImpersonationToken::query()->where('token', hash('sha256', $rawToken))->count())->toBe(0);
});

test('aborts on expired token', function () {
    ImpersonationToken::factory()->create([
        'token' => hash('sha256', 'expired-token'),
        'tenant_id' => 'test',
        'expires_at' => now()->subMinutes(5),
    ]);

    resolve(ConsumeImpersonationToken::class)('expired-token');
})->throws(Symfony\Component\HttpKernel\Exception\HttpException::class);
