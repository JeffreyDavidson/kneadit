<?php

use App\Enums\Customers\ReferralStatus;
use App\Models\Customers\Referral;
use App\Models\Platform\Tenant;

beforeEach(fn () => setUpCentralTest());

test('status is cast to ReferralStatus enum', function () {
    $referrer = Tenant::factory()->create();
    $referral = Referral::factory()->create([
        'referrer_tenant_id' => $referrer->id,
        'status' => ReferralStatus::Pending,
    ]);

    $referral->refresh();

    expect($referral->status)->toBeInstanceOf(ReferralStatus::class)
        ->and($referral->status)->toBe(ReferralStatus::Pending);
});

test('referrer relationship returns the referring tenant', function () {
    $tenant = Tenant::factory()->create();
    $referral = Referral::factory()->create([
        'referrer_tenant_id' => $tenant->id,
    ]);

    expect($referral->referrer->id)->toBe($tenant->id);
});

test('referred relationship returns the referred tenant', function () {
    $referrer = Tenant::factory()->create();
    $referred = Tenant::factory()->create();

    $referral = Referral::factory()->create([
        'referrer_tenant_id' => $referrer->id,
        'referred_tenant_id' => $referred->id,
    ]);

    expect($referral->referred->id)->toBe($referred->id);
});

test('referred relationship returns null when no referred tenant', function () {
    $referrer = Tenant::factory()->create();
    $referral = Referral::factory()->create([
        'referrer_tenant_id' => $referrer->id,
        'referred_tenant_id' => null,
    ]);

    expect($referral->referred)->toBeNull();
});
