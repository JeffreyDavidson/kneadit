<?php

use App\Enums\Customers\ReferralStatus;
use App\Enums\Platform\SubscriptionTier;
use App\Events\Platform\TenantOnboarded;
use App\Models\Customers\Referral;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

pest()->use(DatabaseMigrations::class);

beforeEach(function () {
    Mail::fake();
    setUpTenantTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);

    DB::purge('central');
    $pdo = DB::connection('sqlite')->getPdo();
    DB::connection('central')->setPdo($pdo)->setReadPdo($pdo);

    createCentralTables();
    $this->subdomainCounter = 0;
});

/** @param array<string, mixed> $overrides */
function createSignupUser(array $overrides = []): User
{
    return User::factory()->create($overrides);
}

function uniqueSubdomain(): string
{
    test()->subdomainCounter++;

    return 'testbakery' . test()->subdomainCounter;
}

/**
 * @param array<string, mixed> $data
 * @return TestResponse<Symfony\Component\HttpFoundation\Response>
 */
function submitOnboarding(User $user, array $data = []): TestResponse
{
    if (! isset($data['subdomain'])) {
        $data['subdomain'] = uniqueSubdomain();
    }

    $payload = array_merge([
        'store_name' => 'My Test Bakery',
        'storefront_choice' => 'kneadit',
    ], $data);

    return actingAs($user)->post(route('onboarding.store'), $payload);
}

// -------------------------------------------------------
// Registration & Auth — Billing Plans
// -------------------------------------------------------

test('guest viewing billing plans is redirected', function () {
    $response = get(route('billing.plans'));
    expect($response->getStatusCode())->not->toBe(200);
});

test('authenticated user can view billing plans page', function () {
    $user = createSignupUser();
    $response = actingAs($user)->get(route('billing.plans'));
    $response->assertOk();
});

test('authenticated user can initiate checkout with valid plan', function () {
    $user = createSignupUser();
    $response = actingAs($user)->post(route('billing.checkout', 'starter'));
    expect($response->getStatusCode())->not->toBe(422);
});

test('invalid plan name returns error', function () {
    $user = createSignupUser();
    $response = actingAs($user)->post(route('billing.checkout', 'nonexistent'));
    $response->assertNotFound();
});

// -------------------------------------------------------
// Onboarding Flow
// -------------------------------------------------------

test('guest cannot access onboarding', function () {
    $response = get(route('onboarding.show'));
    expect($response->getStatusCode())->not->toBe(200);
});

test('authenticated user can view onboarding page', function () {
    $user = createSignupUser();
    $response = actingAs($user)->get(route('onboarding.show'));
    $response->assertOk();
});

test('successful onboarding completes the default KneadIt pipeline', function () {
    Event::fake([TenantOnboarded::class]);

    $user = createSignupUser();
    $sub = uniqueSubdomain();
    $tokenBefore = csrf_token();

    $response = submitOnboarding($user, [
        'subdomain' => $sub,
        'store_name' => 'Artisan Breads',
    ]);

    $domain = DB::connection('central')->table('domains')
        ->where('tenant_id', $sub)->first();
    throw_unless($domain instanceof stdClass, RuntimeException::class, 'Expected the tenant domain to exist.');

    $tenant = Tenant::query()->findOrFail($sub);
    $tenant->run(function () use ($user) {
        test()->assertDatabaseHas('users', ['email' => $user->email]);
        test()->assertDatabaseHas('settings', [
            'key' => 'store_name',
            'value' => 'Artisan Breads',
        ]);
        test()->assertDatabaseHas('settings', [
            'key' => 'store_email',
            'value' => $user->email,
        ]);
    });

    test()->assertDatabaseHas('tenants', ['id' => $sub]);
    test()->assertDatabaseHas('tenants', [
        'id' => $sub,
        'plan' => SubscriptionTier::Starter,
    ]);

    $tenantRow = DB::table('tenants')->where('id', $sub)->first();
    throw_unless($tenantRow instanceof stdClass && is_string($tenantRow->trial_ends_at), RuntimeException::class, 'Expected the tenant and trial end date to exist.');
    $trialEnds = Date::parse($tenantRow->trial_ends_at);
    $host = parse_url(Config::string('app.url'), PHP_URL_HOST);

    expect($domain)->not->toBeNull()
        ->and($domain->domain)->toBe($sub)
        ->and($tenantRow->storefront_enabled)->toBeTruthy()
        ->and($trialEnds->isBetween(now()->addDays(29), now()->addDays(31)))->toBeTrue()
        ->and(auth()->check())->toBeFalse()
        ->and(csrf_token())->not->toBe($tokenBefore);

    $response->assertRedirect('http://' . $sub . '.' . $host . '/admin');

    Event::assertDispatched(TenantOnboarded::class, function (TenantOnboarded $event) use ($user, $sub) {
        return $event->user->is($user)
            && $event->tenant->id === $sub
            && str_contains($event->adminUrl, "{$sub}.")
            && str_ends_with($event->adminUrl, '/admin');
    });
});

test('onboarding with an external storefront stores its URL and disables the KneadIt storefront', function () {
    $user = createSignupUser();
    $sub = uniqueSubdomain();

    submitOnboarding($user, [
        'subdomain' => $sub,
        'storefront_choice' => 'own',
        'external_website' => 'https://mybakery.com',
    ]);

    test()->assertDatabaseHas('tenants', [
        'id' => $sub,
        'external_website' => 'https://mybakery.com',
    ]);

    $tenant = DB::table('tenants')->where('id', $sub)->first();
    throw_unless($tenant instanceof stdClass, RuntimeException::class, 'Expected the tenant to exist.');

    expect($tenant->storefront_enabled)->toBeFalsy();
});

test('duplicate subdomain returns validation error', function () {
    $sub = uniqueSubdomain();
    DB::table('tenants')->insert([
        'id' => $sub,
        'name' => 'Existing',
        'email' => 'existing@example.com',
        'plan' => SubscriptionTier::Starter,
        'is_active' => true,
        'storefront_enabled' => true,
        'brand_color_primary' => '#d4920c',
        'brand_color_secondary' => '#1c1410',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('domains')->insert([
        'domain' => $sub,
        'tenant_id' => $sub,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $user = createSignupUser();
    $response = actingAs($user)->post(route('onboarding.store'), [
        'store_name' => 'My Bakery',
        'subdomain' => $sub,
        'storefront_choice' => 'kneadit',
    ]);
    $response->assertSessionHasErrors('subdomain');
});

test('missing store name returns validation error', function () {
    $user = createSignupUser();
    $response = actingAs($user)->post(route('onboarding.store'), [
        'subdomain' => 'testshop',
        'storefront_choice' => 'kneadit',
    ]);
    $response->assertSessionHasErrors('store_name');
});

test('missing subdomain returns validation error', function () {
    $user = createSignupUser();
    $response = actingAs($user)->post(route('onboarding.store'), [
        'store_name' => 'My Bakery',
        'storefront_choice' => 'kneadit',
    ]);
    $response->assertSessionHasErrors('subdomain');
});

test('invalid storefront choice returns validation error', function () {
    $user = createSignupUser();
    $response = actingAs($user)->post(route('onboarding.store'), [
        'store_name' => 'My Bakery',
        'subdomain' => 'testshop',
        'storefront_choice' => 'invalid',
    ]);
    $response->assertSessionHasErrors('storefront_choice');
});

test('storefront choice own without external website returns validation error', function () {
    $user = createSignupUser();
    $response = actingAs($user)->post(route('onboarding.store'), [
        'store_name' => 'My Bakery',
        'subdomain' => 'testshop',
        'storefront_choice' => 'own',
    ]);
    $response->assertSessionHasErrors('external_website');
});

test('subdomain is lowercased', function () {
    $user = createSignupUser();
    $this->subdomainCounter++;
    $sub = 'MyBaKeRy' . $this->subdomainCounter;
    $lower = strtolower($sub);

    submitOnboarding($user, ['subdomain' => $sub]);

    test()->assertDatabaseHas('tenants', ['id' => $lower]);
});

test('referral code from session is forwarded to CompleteReferral', function () {
    $referrer = Tenant::factory()->create();
    $referral = Referral::factory()->create([
        'referral_code' => 'SESSREF1',
        'referrer_tenant_id' => $referrer->id,
    ]);

    $user = createSignupUser();
    $sub = uniqueSubdomain();
    actingAs($user)
        ->withSession(['referral_code' => 'SESSREF1'])
        ->post(route('onboarding.store'), [
            'store_name' => 'Sess Ref Bakery',
            'subdomain' => $sub,
            'storefront_choice' => 'kneadit',
        ]);

    expect($referral->fresh())
        ->status->toBe(ReferralStatus::Completed)
        ->referred_tenant_id->toBe($sub)
        ->referred_email->toBe($user->email);
});
