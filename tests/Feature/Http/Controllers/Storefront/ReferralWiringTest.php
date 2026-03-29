<?php

use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
    config(['database.connections.central' => config('database.connections.sqlite')]);

    DB::purge('central');
    $pdo = DB::connection('sqlite')->getPdo();
    DB::connection('central')->setPdo($pdo)->setReadPdo($pdo);

    createCentralTables();
});

test('referral tracking route stores code in session', function () {
    createTenant(['id' => 'referrer-bakery', 'name' => 'Referrer', 'email' => 'ref@test.com']);
    DB::table('referrals')->insert([
        'referrer_tenant_id' => 'referrer-bakery',
        'referral_code' => 'sweet-abc1',
        'status' => OrderStatus::Pending,
        'reward_months' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = get('/ref/sweet-abc1');

    $response->assertRedirect('/register');
    $response->assertSessionHas('referral_code', 'sweet-abc1');
});

test('referral tracking with invalid code still redirects', function () {
    $response = get('/ref/nonexistent-code');

    $response->assertRedirect('/register');
});

test('onboarding wires referral completion', function () {
    $source = file_get_contents(app_path('Http/Controllers/OnboardingController.php'));

    expect($source)->toContain('referral_code')->toContain('CompleteReferral');
});
