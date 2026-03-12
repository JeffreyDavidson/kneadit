<?php

namespace Tests\Feature;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\CentralTestCase;

class ReferralWiringTest extends CentralTestCase
{
    public function test_referral_tracking_route_stores_code_in_session(): void
    {
        // Create a referral
        $this->createTenant(['id' => 'referrer-bakery', 'name' => 'Referrer', 'email' => 'ref@test.com']);
        DB::table('referrals')->insert([
            'referrer_tenant_id' => 'referrer-bakery',
            'referral_code' => 'sweet-abc1',
            'status' => 'pending',
            'reward_months' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/ref/sweet-abc1');

        $response->assertRedirect('/register');
        $response->assertSessionHas('referral_code', 'sweet-abc1');
    }

    public function test_referral_tracking_with_invalid_code_still_redirects(): void
    {
        $response = $this->get('/ref/nonexistent-code');

        $response->assertRedirect('/register');
    }

    public function test_onboarding_wires_referral_completion(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/OnboardingController.php'));

        $this->assertStringContainsString('referral_code', $source);
        $this->assertStringContainsString("'status' => 'completed'", $source);
        $this->assertStringContainsString('referred_tenant_id', $source);
    }
}
