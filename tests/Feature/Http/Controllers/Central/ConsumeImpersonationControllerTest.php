<?php

use App\Actions\Platform\ConsumeImpersonationToken;
use App\Http\Controllers\Central\ConsumeImpersonationController;
use App\Models\Staff\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use JMac\Testing\Double;

function impersonationRequest(string $path): Request
{
    $request = Request::create($path, 'GET');
    $request->server->set('REMOTE_ADDR', '10.0.0.1');
    $request->setLaravelSession(new Store('test', new ArraySessionHandler(60)));

    return $request;
}

beforeEach(function () {
    setUpCentralTest();

    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
});

test('consume impersonation logs in user and redirects to admin', function () {
    $user = User::factory()->owner()->create();
    $request = impersonationRequest('/impersonate/valid-token-123');

    $action = Double::for(ConsumeImpersonationToken::class);
    $action->expects('__invoke')
        ->with('valid-token-123', '10.0.0.1')
        ->returns($user);

    $controller = new ConsumeImpersonationController;
    $response = $controller('valid-token-123', $request, $action);

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getTargetUrl())->toContain('/admin')
        ->and(auth()->user()->id)->toBe($user->id);
});

test('consume impersonation flushes prior session data', function () {
    $user = User::factory()->owner()->create();
    $request = impersonationRequest('/impersonate/valid-token-123');

    // Simulate a prior platform-admin session with a stale password hash —
    // without flushing this, AuthenticateSession on the next request would
    // compare it to the impersonated user's hash and bounce them to login.
    $request->session()->put('password_hash_web', 'platform-admin-old-hash');
    $request->session()->put('login_web_xyz', 999);

    $action = Double::for(ConsumeImpersonationToken::class);
    $action->expects('__invoke')->returns($user);

    (new ConsumeImpersonationController)('valid-token-123', $request, $action);

    expect($request->session()->get('password_hash_web'))->not->toBe('platform-admin-old-hash')
        ->and($request->session()->get('login_web_xyz'))->toBeNull();
});

test('consume impersonation aborts for invalid token', function () {
    $request = impersonationRequest('/impersonate/bad-token');

    $action = Double::for(ConsumeImpersonationToken::class);
    $action->expects('__invoke')
        ->throws(
            new Symfony\Component\HttpKernel\Exception\HttpException(403, 'Invalid or expired impersonation token.'),
        );

    $controller = new ConsumeImpersonationController;

    expect(fn () => $controller('bad-token', $request, $action))
        ->toThrow(Symfony\Component\HttpKernel\Exception\HttpException::class);
});
