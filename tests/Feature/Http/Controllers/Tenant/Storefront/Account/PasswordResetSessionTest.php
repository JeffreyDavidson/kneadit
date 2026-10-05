<?php

use App\Models\Customers\Customer;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

use function Pest\Laravel\withoutMiddleware;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

/**
 * Sign the customer in the way a browser would (a remembered login), and hand
 * back what that browser holds: the session data. The test client keeps one
 * session, so it is cleared afterwards to stand in for "another client".
 *
 * @return array<string, mixed>
 */
function signInOnAnotherClient(Customer $customer, string $password): array
{
    withoutMiddleware(tenantMiddleware())
        ->post(route('account.login', [], false), [
            'email' => $customer->email,
            'password' => $password,
            'remember' => '1',
        ])
        ->assertRedirect(route('account.dashboard', [], false));

    $session = session()->all();

    test()->flushSession();
    auth()->forgetGuards();

    return $session;
}

function completePasswordReset(Customer $customer, string $newPassword): void
{
    withoutMiddleware(tenantMiddleware())
        ->post(route('account.password.update', [], false), [
            'token' => Password::broker('customers')->createToken($customer),
            'email' => $customer->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ])
        ->assertRedirect(route('account.login.show', [], false));

    test()->flushSession();
    auth()->forgetGuards();
}

dataset('account pages', [
    'dashboard' => 'account.dashboard',
    'orders' => 'account.orders',
    'profile' => 'account.profile.show',
]);

test('a session signed in before a password reset is signed out afterwards', function (string $routeName) {
    $customer = Customer::factory()->verified()->withPassword('old-password-1')->create(['email' => 'jane@example.com']);
    $session = signInOnAnotherClient($customer, 'old-password-1');

    completePasswordReset($customer, 'new-password-1');

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession($session)
        ->get(route($routeName, [], false));

    $response->assertRedirect(route('account.login.show', [], false));
    expect(auth('customer')->check())->toBeFalse();
})->with('account pages');

test('a session that was used before the password reset is signed out afterwards', function () {
    $customer = Customer::factory()->verified()->withPassword('old-password-1')->create(['email' => 'jane@example.com']);
    $session = signInOnAnotherClient($customer, 'old-password-1');

    withoutMiddleware(tenantMiddleware())->withSession($session)->get(route('account.dashboard', [], false))->assertOk();
    $session = session()->all();
    test()->flushSession();
    auth()->forgetGuards();

    completePasswordReset($customer, 'new-password-1');

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession($session)
        ->get(route('account.dashboard', [], false));

    $response->assertRedirect(route('account.login.show', [], false));
});

test('a session signed in before a password reset cannot reach the customer API afterwards', function () {
    $customer = Customer::factory()->verified()->withPassword('old-password-1')->create(['email' => 'jane@example.com']);
    $session = signInOnAnotherClient($customer, 'old-password-1');

    completePasswordReset($customer, 'new-password-1');

    withoutMiddleware(tenantMiddleware())
        ->withSession($session)
        ->getJson(route('api.favorites.index', [], false));

    expect(auth('customer')->check())->toBeFalse();
});

test('the customer can sign in again with the new password after the reset', function () {
    $customer = Customer::factory()->verified()->withPassword('old-password-1')->create(['email' => 'jane@example.com']);

    completePasswordReset($customer, 'new-password-1');

    withoutMiddleware(tenantMiddleware())
        ->post(route('account.login', [], false), [
            'email' => 'jane@example.com',
            'password' => 'new-password-1',
        ])
        ->assertRedirect(route('account.dashboard', [], false));

    withoutMiddleware(tenantMiddleware())
        ->get(route('account.dashboard', [], false))
        ->assertOk();
});

test('a session signed in after the password reset stays signed in', function () {
    $customer = Customer::factory()->verified()->withPassword('old-password-1')->create(['email' => 'jane@example.com']);

    completePasswordReset($customer, 'new-password-1');
    $session = signInOnAnotherClient($customer, 'new-password-1');

    withoutMiddleware(tenantMiddleware())
        ->withSession($session)
        ->get(route('account.dashboard', [], false))
        ->assertOk();
});

test('a remember cookie issued before a password reset no longer signs the customer in', function () {
    $customer = Customer::factory()->verified()->withPassword('old-password-1')->create([
        'email' => 'jane@example.com',
        'remember_token' => 'stale-remember-token',
    ]);
    $recaller = "{$customer->getKey()}|stale-remember-token|{$customer->getAuthPassword()}";

    completePasswordReset($customer, 'new-password-1');

    $response = withoutMiddleware(tenantMiddleware())
        ->withCookie(auth('customer')->getRecallerName(), $recaller)
        ->get(route('account.dashboard', [], false));

    $response->assertRedirect(route('account.login.show', [], false));
});

test('resetting the password rotates the remember token and fires the PasswordReset event', function () {
    Event::fake([PasswordReset::class]);

    $customer = Customer::factory()->verified()->withPassword('old-password-1')->create([
        'email' => 'jane@example.com',
        'remember_token' => 'stale-remember-token',
    ]);

    completePasswordReset($customer, 'new-password-1');

    $customer->refresh();

    expect($customer->remember_token)->not->toBe('stale-remember-token')
        ->and($customer->remember_token)->toHaveLength(60)
        ->and(Hash::check('new-password-1', $customer->password))->toBeTrue();
    Event::assertDispatched(PasswordReset::class, fn (PasswordReset $event): bool => $event->user->is($customer));
});
