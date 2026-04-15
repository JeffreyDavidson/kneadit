<?php

use App\Enums\Platform\SubscriptionTier;
use App\Enums\Staff\UserRole;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

function createAdmin(): User
{
    return User::factory()->create(['role' => UserRole::PlatformAdmin]);
}

function insertTenant(string $id = 'test-bakery'): string
{
    DB::table('tenants')->insert([
        'id' => $id,
        'name' => 'Test Owner',
        'email' => 'test@example.com',
        'plan' => SubscriptionTier::Starter,
        'data' => '{}',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $id;
}

beforeEach(function () {
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
    config(['database.connections.central' => config('database.connections.sqlite')]);

    DB::purge('central');
    $pdo = DB::connection('sqlite')->getPdo();
    DB::connection('central')->setPdo($pdo)->setReadPdo($pdo);

    createCentralTables();
});

test('unauthenticated request returns 403', function () {
    $id = insertTenant();

    $response = get(route('central.export', [$id, 'products']));

    $response->assertForbidden();
});

test('invalid export type returns 404', function () {
    $id = insertTenant();

    $response = actingAs(createAdmin())
        ->get(route('central.export', [$id, 'invalid']));

    $response->assertNotFound();
});

test('products csv export returns correct content type', function () {
    $id = insertTenant();

    $response = actingAs(createAdmin())
        ->get(route('central.export', [$id, 'products']));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
});

test('categories csv export works', function () {
    $id = insertTenant();

    $response = actingAs(createAdmin())
        ->get(route('central.export', [$id, 'categories']));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
});

test('orders csv export works', function () {
    $id = insertTenant();

    $response = actingAs(createAdmin())
        ->get(route('central.export', [$id, 'orders']));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
});

test('customers csv export works', function () {
    $id = insertTenant();

    $response = actingAs(createAdmin())
        ->get(route('central.export', [$id, 'customers']));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
});

test('reviews csv export works', function () {
    $id = insertTenant();

    $response = actingAs(createAdmin())
        ->get(route('central.export', [$id, 'reviews']));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
});

test('all zip export returns zip content type', function () {
    $id = insertTenant();

    $response = actingAs(createAdmin())
        ->get(route('central.export', [$id, 'all']));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/zip');
});

test('non-existent tenant returns 404', function () {
    $response = actingAs(createAdmin())
        ->get(route('central.export', ['non-existent-tenant', 'products']));

    $response->assertNotFound();
});

test('non-admin user is forbidden from exporting', function () {
    $id = insertTenant();
    $user = User::factory()->create(['role' => UserRole::Owner]);

    $response = actingAs($user)
        ->get(route('central.export', [$id, 'products']));

    $response->assertForbidden();
});
