<?php

use App\Models\Staff\User;
use App\Notifications\Platform\OwnerVerifyEmailNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\URL;

$centralUrl = env('BROWSER_TEST_CENTRAL_URL', 'https://app.getkneadit.test');

/**
 * The served central app writes to the real central database, while this test
 * process runs on an in-memory one. Read the user the browser just registered
 * through a connection to the served database.
 *
 * @return Builder<User>
 */
function servedCentralUsers(): Builder
{
    config([
        'database.connections.served_central' => [
            ...config('database.connections.central'),
            'database' => env('BROWSER_TEST_CENTRAL_DATABASE', database_path('database.sqlite')),
        ],
    ]);

    return User::on('served_central')->newQuery();
}

/**
 * The link the verification email carries, signed for the central host the
 * browser is using.
 */
function ownerVerificationUrl(string $centralUrl, string $email): string
{
    $user = servedCentralUsers()->where('email', $email)->firstOrFail();

    URL::forceRootUrl($centralUrl);

    try {
        /** @var string $url */
        $url = new OwnerVerifyEmailNotification()->toMail($user)->viewData['verificationUrl'];
    } finally {
        URL::forceRootUrl(null);
    }

    return $url;
}

// Walks a new owner through the central site in a real browser: register, land
// on "Check your email", get turned away from bakery setup until the address is
// verified, then follow the emailed link into bakery setup. The bakery itself
// lives on a new subdomain the smoke servers can't reach, so the journey stops
// at the setup form.

test('a new owner registers, is blocked until the email is verified, then reaches bakery setup', function () use ($centralUrl) {
    $email = 'signup-journey-'.uniqid().'@example.com';

    try {
        visit("{$centralUrl}/register")
            ->fill('input[name="name"]', 'Signup Journey Owner')
            ->fill('input[name="email"]', $email)
            ->fill('input[name="bakery_name"]', 'Signup Journey Bakery')
            ->fill('input[name="password"]', 'SecurePass123!')
            ->fill('input[name="password_confirmation"]', 'SecurePass123!')
            ->click('input[name="terms"]')
            ->press('Create Account')
            ->assertPathIs('/email/verify')
            ->assertSee('Check your email')
            ->assertSee($email)
            ->assertSee('Resend Verification Email')
            ->assertNoJavaScriptErrors()
            ->navigate("{$centralUrl}/onboarding")
            ->assertPathIs('/email/verify')
            ->assertSee('Check your email')
            ->assertNoJavaScriptErrors()
            ->navigate(ownerVerificationUrl($centralUrl, $email))
            ->assertPathIs('/onboarding')
            ->assertVisible('input[name="store_name"]')
            ->assertVisible('input[name="subdomain"]')
            ->assertSee('Create My Bakery')
            ->assertNoJavaScriptErrors();
    } finally {
        servedCentralUsers()->where('email', $email)->delete();
    }
})->group('launch-smoke');
