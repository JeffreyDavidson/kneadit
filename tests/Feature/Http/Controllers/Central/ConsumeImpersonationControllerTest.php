<?php

use App\Actions\Platform\ConsumeImpersonationToken;
use App\Http\Controllers\Central\ConsumeImpersonationController;
use App\Models\Staff\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

beforeEach(function () {
    setUpCentralTest();

    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
});

test('consume impersonation logs in user and redirects to admin', function () {
    $user = User::factory()->owner()->create();
    $request = Request::create('/impersonate/valid-token-123', 'GET');
    $request->server->set('REMOTE_ADDR', '10.0.0.1');

    $action = Mockery::mock(ConsumeImpersonationToken::class);
    $action->shouldReceive('__invoke')
        ->once()
        ->with('valid-token-123', '10.0.0.1')
        ->andReturn($user);

    $controller = new ConsumeImpersonationController;
    $response = $controller('valid-token-123', $request, $action);

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getTargetUrl())->toContain('/admin')
        ->and(auth()->user()->id)->toBe($user->id);
});

test('consume impersonation aborts for invalid token', function () {
    $request = Request::create('/impersonate/bad-token', 'GET');

    $action = Mockery::mock(ConsumeImpersonationToken::class);
    $action->shouldReceive('__invoke')
        ->once()
        ->andThrow(
            new Symfony\Component\HttpKernel\Exception\HttpException(403, 'Invalid or expired impersonation token.'),
        );

    $controller = new ConsumeImpersonationController;

    expect(fn () => $controller('bad-token', $request, $action))
        ->toThrow(Symfony\Component\HttpKernel\Exception\HttpException::class);
});
