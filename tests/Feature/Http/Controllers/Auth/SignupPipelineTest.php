<?php

use App\Enums\Platform\SubscriptionTier;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(DatabaseMigrations::class);

beforeEach(function () {
    Mail::fake();
    setUpTenantTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);

    DB::purge('central');
    $pdo = DB::connection('sqlite')->getPdo();
    DB::connection('central')->setPdo($pdo)->setReadPdo($pdo);

    createCentralTables();
    $this->createdSubdomains = [];
    $this->subdomainCounter = 0;
});

afterEach(function () {
    foreach ($this->createdSubdomains as $subdomain) {
        @unlink(database_path("tenant{$subdomain}"));
    }
});

function createSignupUser(array $overrides = []): User
{
    return User::factory()->create($overrides);
}

function uniqueSubdomain(): string
{
    test()->subdomainCounter++;
    $sub = 'testbakery' . test()->subdomainCounter;
    $subs = test()->createdSubdomains;
    $subs[] = $sub;
    test()->createdSubdomains = $subs;

    return $sub;
}

function submitOnboarding(User $user, array $data = []): TestResponse
{
    if (! isset($data['subdomain'])) {
        $data['subdomain'] = uniqueSubdomain();
    } else {
        $sub = strtolower($data['subdomain']);
        if (! in_array($sub, test()->createdSubdomains)) {
            $subs = test()->createdSubdomains;
            $subs[] = $sub;
            test()->createdSubdomains = $subs;
        }
    }

    $payload = array_merge([
        'store_name' => 'My Test Bakery',
        'storefront_choice' => 'kneadit',
    ], $data);

    return actingAs($user)->post(route('onboarding.store'), $payload);
}

function mockTenantWithPlan(string $plan): void
{
    $tenant = Tenant::query()->make([
        'id' => "test-{$plan}",
        'name' => 'Test',
        'email' => 'test@example.com',
        'plan' => $plan,
    ]);

    app()->instance(Stancl\Tenancy\Contracts\Tenant::class, $tenant);
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

test('successful onboarding creates tenant record', function () {
    $user = createSignupUser();
    $sub = uniqueSubdomain();
    submitOnboarding($user, ['subdomain' => $sub]);

    test()->assertDatabaseHas('tenants', ['id' => $sub]);
});

test('successful onboarding creates domain record with correct subdomain', function () {
    $user = createSignupUser();
    $sub = uniqueSubdomain();
    submitOnboarding($user, ['subdomain' => $sub]);

    $domain = DB::connection('central')->table('domains')
        ->where('tenant_id', $sub)->first();

    expect($domain)->not->toBeNull()->and($domain->domain)->toBe($sub);
});

test('successful onboarding creates tenant user with same email', function () {
    $user = createSignupUser();
    $sub = uniqueSubdomain();
    submitOnboarding($user, ['subdomain' => $sub]);

    $tenant = Tenant::query()->find($sub);
    $tenant->run(function () use ($user) {
        test()->assertDatabaseHas('users', ['email' => $user->email]);
    });
});

test('successful onboarding seeds store name setting in tenant', function () {
    $user = createSignupUser();
    $sub = uniqueSubdomain();
    submitOnboarding($user, ['subdomain' => $sub, 'store_name' => 'Artisan Breads']);

    $tenant = Tenant::query()->find($sub);
    $tenant->run(function () {
        test()->assertDatabaseHas('settings', [
            'key' => 'store_name',
            'value' => 'Artisan Breads',
        ]);
    });
});

test('successful onboarding seeds store email setting in tenant', function () {
    $user = createSignupUser();
    $sub = uniqueSubdomain();
    submitOnboarding($user, ['subdomain' => $sub]);

    $tenant = Tenant::query()->find($sub);
    $tenant->run(function () use ($user) {
        test()->assertDatabaseHas('settings', [
            'key' => 'store_email',
            'value' => $user->email,
        ]);
    });
});

test('onboarding with storefront choice own stores external website', function () {
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
});

test('onboarding with storefront choice kneadit sets storefront enabled true', function () {
    $user = createSignupUser();
    $sub = uniqueSubdomain();
    submitOnboarding($user, ['subdomain' => $sub, 'storefront_choice' => 'kneadit']);

    $tenant = DB::table('tenants')->where('id', $sub)->first();
    expect($tenant->storefront_enabled)->toBeTruthy();
});

test('onboarding with storefront choice own sets storefront enabled false', function () {
    $user = createSignupUser();
    $sub = uniqueSubdomain();
    submitOnboarding($user, [
        'subdomain' => $sub,
        'storefront_choice' => 'own',
        'external_website' => 'https://mybakery.com',
    ]);

    $tenant = DB::table('tenants')->where('id', $sub)->first();
    expect($tenant->storefront_enabled)->toBeFalsy();
});

test('onboarding redirects to tenant admin url', function () {
    $user = createSignupUser();
    $sub = uniqueSubdomain();
    $response = submitOnboarding($user, ['subdomain' => $sub]);

    $host = parse_url(config('app.url'), PHP_URL_HOST);
    $response->assertRedirect('http://' . $sub . '.' . $host . '/admin');
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
    $this->createdSubdomains[] = $lower;

    submitOnboarding($user, ['subdomain' => $sub]);

    test()->assertDatabaseHas('tenants', ['id' => $lower]);
});

test('tenant gets starter plan by default', function () {
    $user = createSignupUser();
    $sub = uniqueSubdomain();
    submitOnboarding($user, ['subdomain' => $sub]);

    test()->assertDatabaseHas('tenants', [
        'id' => $sub,
        'plan' => SubscriptionTier::Starter,
    ]);
});

test('tenant gets trial ends at set to 30 days from now', function () {
    $user = createSignupUser();
    $sub = uniqueSubdomain();
    submitOnboarding($user, ['subdomain' => $sub]);

    $tenant = DB::table('tenants')->where('id', $sub)->first();
    $trialEnds = Date::parse($tenant->trial_ends_at);

    expect(
        $trialEnds->isBetween(now()->addDays(29), now()->addDays(31)),
    )->toBeTrue("Trial ends at {$trialEnds} is not approximately 30 days from now");
});

// -------------------------------------------------------
// Plan Gating (post-signup)
// -------------------------------------------------------
