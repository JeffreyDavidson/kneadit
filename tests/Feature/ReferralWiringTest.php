<?php

use Illuminate\Support\Facades\DB;

use function Pest\Laravel\get;

beforeEach(function () {
    setUpCentralTest();
});

test('referral tracking route stores code in session', function () {
    createTenant(['id' => 'referrer-bakery', 'name' => 'Referrer', 'email' => 'ref@test.com']);
    DB::table('referrals')->insert([
        'referrer_tenant_id' => 'referrer-bakery',
        'referral_code' => 'sweet-abc1',
        'status' => 'pending',
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

    expect($source)->toContain('referral_code');
    expect($source)->toContain("'status' => 'completed'");
    expect($source)->toContain('referred_tenant_id');
});
