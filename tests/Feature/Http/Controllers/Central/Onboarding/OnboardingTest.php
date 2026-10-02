<?php

use App\Actions\Tenants\CompleteTenantOnboarding;
use App\Console\Commands\Tenants\ProvisionTestTenantCommand;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

dataset('invalid onboarding subdomains', [
    'underscore' => 'my_bakery',
    'leading hyphen' => '-bakery',
    'trailing hyphen' => 'bakery-',
    'demo tenant id' => Tenant::DEMO_ID,
    'browser test tenant id' => ProvisionTestTenantCommand::TENANT_ID,
    'central app host' => 'app-staging',
]);

function onboardingPayload(string $subdomain): array
{
    return [
        'store_name' => 'Sweet Treats',
        'subdomain' => $subdomain,
        'storefront_choice' => 'kneadit',
    ];
}

function expectNoTenantToBeCreated(): void
{
    $completeOnboarding = Mockery::mock(CompleteTenantOnboarding::class);
    $completeOnboarding->shouldNotReceive('__invoke');
    app()->instance(CompleteTenantOnboarding::class, $completeOnboarding);
}

test('onboarding page renders for authenticated user', function () {
    $user = User::factory()->owner()->create();

    actingAs($user)
        ->get(route('onboarding.show'))
        ->assertOk();
});

test('onboarding rejects subdomains that are not valid hostname labels or are reserved', function (string $subdomain) {
    expectNoTenantToBeCreated();
    $user = User::factory()->owner()->create();

    $response = actingAs($user)
        ->post(route('onboarding.store'), onboardingPayload($subdomain));

    $response->assertSessionHasErrors('subdomain');
    $response->assertSessionHasInput('subdomain', $subdomain);
})->with('invalid onboarding subdomains');

test('onboarding explains the hostname format when the subdomain is malformed', function () {
    expectNoTenantToBeCreated();
    $user = User::factory()->owner()->create();

    $response = actingAs($user)
        ->post(route('onboarding.store'), onboardingPayload('my_bakery'));

    $response->assertSessionHasErrors([
        'subdomain' => 'Use lowercase letters, numbers and hyphens, starting and ending with a letter or number.',
    ]);
});

test('onboarding rejects a subdomain that differs from an existing domain only by case', function () {
    expectNoTenantToBeCreated();
    $tenant = Tenant::factory()->create(['id' => 'foo']);
    $tenant->domains()->create(['domain' => 'foo']);
    $user = User::factory()->owner()->create();

    $response = actingAs($user)
        ->post(route('onboarding.store'), onboardingPayload('Foo'));

    $response->assertSessionHasErrors('subdomain');
});

test('onboarding rejects a subdomain that matches a tenant id without a bare domain row', function () {
    expectNoTenantToBeCreated();
    Tenant::factory()->create(['id' => 'orphan']);
    $user = User::factory()->owner()->create();

    $response = actingAs($user)
        ->post(route('onboarding.store'), onboardingPayload('orphan'));

    $response->assertSessionHasErrors('subdomain');
});

test('onboarding lowercases and trims the subdomain before validating and storing it', function () {
    $user = User::factory()->owner()->create();
    $completeOnboarding = Mockery::mock(CompleteTenantOnboarding::class);
    $completeOnboarding->shouldReceive('__invoke')
        ->once()
        ->withArgs(fn (User $actual, string $storeName, string $subdomain): bool => $subdomain === 'sweet-treats')
        ->andReturn(Tenant::factory()->make());
    app()->instance(CompleteTenantOnboarding::class, $completeOnboarding);

    $response = actingAs($user)
        ->post(route('onboarding.store'), onboardingPayload('  Sweet-Treats '));

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();
});

test('every platform tenant id is a reserved subdomain', function (string $tenantId) {
    expect(config('kneadit.reserved_subdomains'))->toContain($tenantId);
})->with([
    'demo' => Tenant::DEMO_ID,
    'browser test' => ProvisionTestTenantCommand::TENANT_ID,
]);
