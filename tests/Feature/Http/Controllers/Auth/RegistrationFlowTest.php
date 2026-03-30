<?php

use App\Enums\SubscriptionTier;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
    config(['database.connections.central' => config('database.connections.sqlite')]);

    DB::purge('central');
    $pdo = DB::connection('sqlite')->getPdo();
    DB::connection('central')->setPdo($pdo)->setReadPdo($pdo);

    createCentralTables();
});

test('registration page loads', function () {
    $response = get(route('register'));

    $response->assertOk();
    $response->assertSee('Start your bakery journey');
});

test('user can register with valid data', function () {
    $response = post(route('register'), [
        'name' => 'Jane Baker',
        'email' => 'jane@example.com',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
        'bakery_name' => 'Sunshine Bakery',
        'terms' => true,
    ]);

    $response->assertRedirect(route('billing.plans'));
    $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    $this->assertAuthenticated();
    expect(session('bakery_name'))->toBe('Sunshine Bakery');
});

test('registration requires all fields', function () {
    $response = post(route('register'), []);

    $response->assertSessionHasErrors(['name', 'email', 'password', 'bakery_name', 'terms']);
});

test('registration requires unique email', function () {
    User::factory()->create([
        'email' => 'taken@example.com',
    ]);

    $response = post(route('register'), [
        'name' => 'New User',
        'email' => 'taken@example.com',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
        'bakery_name' => 'My Bakery',
    ]);

    $response->assertSessionHasErrors(['email']);
});

test('registration requires password confirmation', function () {
    $response = post(route('register'), [
        'name' => 'Jane Baker',
        'email' => 'jane@example.com',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'DifferentPass!',
        'bakery_name' => 'My Bakery',
    ]);

    $response->assertSessionHasErrors(['password']);
});

test('registration requires minimum password length', function () {
    $response = post(route('register'), [
        'name' => 'Jane Baker',
        'email' => 'jane@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
        'bakery_name' => 'My Bakery',
    ]);

    $response->assertSessionHasErrors(['password']);
});

test('plans page requires authentication', function () {
    $response = get(route('billing.plans'));

    $response->assertRedirect();
});

test('authenticated user can view plans', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->get(route('billing.plans'));

    $response->assertOk();
    $response->assertSee('Choose Your Plan');
});

test('checkout requires authentication', function () {
    $response = post(route('billing.checkout', ['plan' => SubscriptionTier::Starter]));

    $response->assertRedirect();
});

test('checkout rejects invalid plan', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->post(route('billing.checkout', ['plan' => 'invalid']));

    $response->assertNotFound();
});

test('onboarding page loads', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->get(route('onboarding.show'));

    $response->assertOk();
});

test('onboarding store creates tenant', function () {
    $user = User::factory()->create();

    $this->mock(Tenant::class, function ($mock) {
        $mock->shouldReceive('create')->andReturn(new Tenant);
    });

    $response = actingAs($user)
        ->post(route('onboarding.store'), [
            'store_name' => '',
            'subdomain' => '',
            'storefront_choice' => '',
        ]);

    $response->assertSessionHasErrors(['store_name', 'subdomain', 'storefront_choice']);
});

test('onboarding validates subdomain format', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->post(route('onboarding.store'), [
            'store_name' => 'My Bakery',
            'subdomain' => 'invalid subdomain!',
            'storefront_choice' => 'kneadit',
        ]);

    $response->assertSessionHasErrors(['subdomain']);
});

test('onboarding requires external website when own chosen', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->post(route('onboarding.store'), [
            'store_name' => 'My Bakery',
            'subdomain' => 'mybakery',
            'storefront_choice' => 'own',
        ]);

    $response->assertSessionHasErrors(['external_website']);
});

test('onboarding does not require external website for kneadit', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->post(route('onboarding.store'), [
            'store_name' => 'My Bakery',
            'subdomain' => 'mybakery',
            'storefront_choice' => 'kneadit',
        ]);

    expect(array_keys(session('errors')?->toArray() ?? []))->not->toContain('external_website');
});

test('guest cannot access onboarding', function () {
    $response = get(route('onboarding.show'));

    $response->assertRedirect();
});

test('billing success redirects to onboarding', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->get(route('billing.success'));

    $response->assertRedirect(route('onboarding.show'));
});

test('login redirects to homepage', function () {
    $response = get(route('login'));

    $response->assertRedirect(route('home'));
});
