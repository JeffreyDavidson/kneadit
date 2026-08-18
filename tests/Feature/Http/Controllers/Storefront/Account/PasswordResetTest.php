<?php

use App\Models\Customers\Customer;
use App\Notifications\Customers\CustomerPasswordResetNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

use function Pest\Laravel\withoutMiddleware;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('forgot-password sends a reset notification when the email matches a customer', function () {
    Notification::fake();

    $customer = Customer::factory()->withPassword()->create(['email' => 'jane@example.com']);

    withoutMiddleware(tenantMiddleware())
        ->post(route('account.password.email', [], false), ['email' => 'jane@example.com']);

    Notification::assertSentTo($customer, CustomerPasswordResetNotification::class);
});

test('forgot-password still reports success for unknown emails to avoid leaking account existence', function () {
    Notification::fake();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('account.password.email', [], false), ['email' => 'nobody@example.com']);

    $response->assertSessionHas('status');
    Notification::assertNothingSent();
});

test('reset-password updates the customer password and redirects to login', function () {
    $customer = Customer::factory()->withPassword('old-password-1')->create(['email' => 'jane@example.com']);

    $token = Password::broker('customers')->createToken($customer);

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('account.password.update', [], false), [
            'token' => $token,
            'email' => 'jane@example.com',
            'password' => 'new-password-1',
            'password_confirmation' => 'new-password-1',
        ]);

    $response->assertRedirect(route('account.login.show', [], false))
        ->assertSessionHas('status');

    expect(Hash::check('new-password-1', $customer->fresh()->password))->toBeTrue();
});

test('reset-password rejects an invalid token', function () {
    Customer::factory()->withPassword()->create(['email' => 'jane@example.com']);

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('account.password.update', [], false), [
            'token' => 'not-a-real-token',
            'email' => 'jane@example.com',
            'password' => 'new-password-1',
            'password_confirmation' => 'new-password-1',
        ]);

    $response->assertSessionHasErrors(['email']);
});

test('reset-password GET renders the form with the token and email from the URL', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('account.password.reset', ['token' => 'abc123', 'email' => 'jane@example.com'], false));

    $response->assertOk()
        ->assertViewIs('storefront.account.reset-password')
        ->assertViewHas('token', 'abc123')
        ->assertViewHas('email', 'jane@example.com');
});

test('reset-password GET defaults email to empty string when not provided', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('account.password.reset', ['token' => 'abc123'], false));

    $response->assertOk()
        ->assertViewHas('email', '');
});

test('notification links to the tenant account reset route', function () {
    $customer = Customer::factory()->withPassword()->create(['email' => 'jane@example.com']);
    $notification = new CustomerPasswordResetNotification('test-token');

    $mail = $notification->toMail($customer);

    expect($mail->actionUrl)->toContain('/account/password/reset/test-token')
        ->and($mail->actionUrl)->toContain('email=jane%40example.com');
});
