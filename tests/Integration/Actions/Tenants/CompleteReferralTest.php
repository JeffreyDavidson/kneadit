<?php

use App\Actions\Tenants\CompleteReferral;
use App\Enums\Customers\ReferralStatus;
use App\Enums\Platform\SubscriptionTier;
use App\Models\Customers\Referral;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    setUpCentralTest();
});

it('completes a pending referral for a tenant', function () {
    DB::table('tenants')->insert([
        ['id' => 'existing-bakery', 'name' => 'Existing Bakery', 'email' => 'existing@test.com', 'plan' => SubscriptionTier::Starter, 'data' => '{}', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 'new-bakery', 'name' => 'New Bakery', 'email' => 'new@test.com', 'plan' => SubscriptionTier::Starter, 'data' => '{}', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $referral = Referral::factory()->create([
        'referral_code' => 'TEST123',
        'referrer_tenant_id' => 'existing-bakery',
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

it('does nothing when referral code is not found', function () {
    $action = new CompleteReferral;
    $action(
        referralCode: 'NONEXISTENT',
        tenantId: 'some-bakery',
        email: 'test@test.com',
    );

    expect(Referral::query()->where('referral_code', 'NONEXISTENT')->exists())->toBeFalse();
});

function seedReferralTenants(array $ids): void
{
    foreach ($ids as $id) {
        DB::table('tenants')->insert([
            'id' => $id,
            'name' => "Bakery {$id}",
            'email' => "{$id}@test.com",
            'plan' => SubscriptionTier::Starter,
            'data' => '{}',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

it('does nothing when the referral code is null', function () {
    seedReferralTenants(['existing-bakery']);
    $referral = Referral::factory()->create([
        'referral_code' => 'NULLCODE1',
        'referrer_tenant_id' => 'existing-bakery',
        'referred_tenant_id' => null,
    ]);

    (new CompleteReferral)(
        referralCode: null,
        tenantId: 'some-bakery',
        email: 'test@test.com',
    );

    expect($referral->fresh()->status)->toBe(ReferralStatus::Pending);
});

it('does not touch a referral that is already completed', function () {
    seedReferralTenants(['existing-bakery', 'previous-bakery']);
    $referral = Referral::factory()->completed()->create([
        'referral_code' => 'CLAIMED1',
        'referrer_tenant_id' => 'existing-bakery',
        'referred_tenant_id' => 'previous-bakery',
        'referred_email' => 'previous@test.com',
    ]);

    (new CompleteReferral)(
        referralCode: 'CLAIMED1',
        tenantId: 'new-bakery',
        email: 'new@test.com',
    );

    expect($referral->fresh())
        ->status->toBe(ReferralStatus::Completed)
        ->referred_tenant_id->toBe('previous-bakery')
        ->referred_email->toBe('previous@test.com');
});

it('does not touch a pending referral whose referred_tenant_id is already set', function () {
    seedReferralTenants(['existing-bakery', 'previous-bakery']);
    $referral = Referral::factory()->create([
        'referral_code' => 'CLAIMEDPENDING1',
        'referrer_tenant_id' => 'existing-bakery',
        'referred_tenant_id' => 'previous-bakery',
        'referred_email' => 'previous@test.com',
    ]);

    (new CompleteReferral)(
        referralCode: 'CLAIMEDPENDING1',
        tenantId: 'new-bakery',
        email: 'new@test.com',
    );

    expect($referral->fresh())
        ->status->toBe(ReferralStatus::Pending)
        ->referred_tenant_id->toBe('previous-bakery')
        ->referred_email->toBe('previous@test.com');
});
