<?php

use App\Actions\Tenants\CompleteReferral;
use App\Enums\ReferralStatus;
use App\Enums\SubscriptionTier;
use App\Models\Referral;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    setUpCentralTest();
});

it('completes a pending referral for a tenant', function () {
    DB::table('tenants')->insert([
        ['id' => 'existing-bakery', 'name' => 'Existing Bakery', 'email' => 'existing@test.com', 'plan' => SubscriptionTier::Starter, 'data' => '{}', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 'new-bakery', 'name' => 'New Bakery', 'email' => 'new@test.com', 'plan' => SubscriptionTier::Starter, 'data' => '{}', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $referral = Referral::query()->create([
        'referral_code' => 'TEST123',
        'referrer_tenant_id' => 'existing-bakery',
        'status' => ReferralStatus::Pending,
    ]);

    $action = new CompleteReferral;
    $action(
        referralCode: 'TEST123',
        tenantId: 'new-bakery',
        email: 'baker@test.com',
    );

    $referral->refresh();

    expect($referral->status)->toBe(ReferralStatus::Completed)->and($referral->referred_tenant_id)->toBe('new-bakery')->and($referral->referred_email)->toBe('baker@test.com');
});
