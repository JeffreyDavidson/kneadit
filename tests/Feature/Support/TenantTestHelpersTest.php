<?php

use App\Mail\Platform\NewSubscriberNotificationMail;
use App\Mail\Platform\WelcomeBakerMail;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

beforeEach(fn () => setUpCentralTest());

test('tenant helpers create central tenant and domain records with domain language', function () {
    $tenant = createTenantWithDomain(
        tenantId: 'sweet-bakery',
        domain: 'sweet-bakery.kneadit.test',
        attributes: [
            'name' => 'Jane Baker',
            'email' => 'jane@example.com',
            'store_name' => 'Sweet Bakery',
        ],
    );

    expect($tenant)->toBeInstanceOf(Tenant::class)
        ->and($tenant->id)->toBe('sweet-bakery')
        ->and($tenant->store_name)->toBe('Sweet Bakery');

    expect(DB::table('domains')->where('domain', 'sweet-bakery.kneadit.test')->where('tenant_id', 'sweet-bakery')->exists())
        ->toBeTrue();
});

test('tenant admin helper creates and authenticates an owner in tenant storage', function () {
    setUpTenantTest();

    $tenant = createTenantWithDomain('admin-bakery');
    $admin = actingAsTenantAdmin($tenant, [
        'name' => 'Admin Baker',
        'email' => 'admin@example.com',
    ]);

    expect($admin)->toBeInstanceOf(User::class)
        ->and($admin->email)->toBe('admin@example.com')
        ->and(auth()->id())->toBe($admin->id);

    expect(User::query()->whereKey($admin->id)->where('email', 'admin@example.com')->exists())->toBeTrue();
});

test('onboarding notification helper asserts queued welcome and platform subscriber mail', function () {
    Mail::fake();

    $tenant = createTenantWithDomain('queued-bakery', attributes: [
        'store_name' => 'Queued Bakery',
    ]);
    $admin = User::factory()->create(['name' => 'Queue Baker', 'email' => 'queue@example.com']);

    queueTenantOnboardingNotifications($admin, $tenant, 'https://queued-bakery.kneadit.test/admin');

    assertNotificationQueued(WelcomeBakerMail::class, fn (WelcomeBakerMail $mail) => $mail->hasTo('queue@example.com'));
    assertNotificationQueued(NewSubscriberNotificationMail::class, fn (NewSubscriberNotificationMail $mail) => $mail->hasTo(Config::string('mail.platform_notify')));
});

test('visitor registration helper creates the user tenant domain and onboarding notifications', function () {
    Mail::fake();

    $registration = registerVisitorAsTenant(
        userAttributes: [
            'name' => 'Jane Baker',
            'email' => 'jane@example.com',
        ],
        tenantAttributes: ['store_name' => 'Jane’s Bakery'],
        tenantId: 'janes-bakery',
        domain: 'janes-bakery.kneadit.test',
    );

    expect($registration['user']->email)->toBe('jane@example.com')
        ->and($registration['tenant']->id)->toBe('janes-bakery')
        ->and($registration['domain'])->toBe('janes-bakery.kneadit.test')
        ->and($registration['admin_url'])->toBe('https://janes-bakery.kneadit.test/admin');

    assertNotificationQueued(WelcomeBakerMail::class);
    assertNotificationQueued(NewSubscriberNotificationMail::class);
});

test('storefront visitor helper sends the tenant domain as the request host', function () {
    $tenant = createTenantWithDomain('host-bakery');

    Route::get('/tenant-helper-host', fn () => request()->getHost());

    visitStorefrontAsTenant($tenant, '/tenant-helper-host')
        ->assertOk()
        ->assertSeeText('host-bakery.kneadit.test');
});
