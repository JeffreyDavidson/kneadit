<?php

use App\Actions\Tenants\GenerateReferralCode;
use App\Models\Referral;
use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    setUpCentralTest();

    if (! Schema::hasTable('referrals')) {
        Schema::create('referrals', function ($table) {
            $table->id();
            $table->string('referrer_tenant_id');
            $table->string('referred_tenant_id')->nullable();
            $table->string('referred_email')->nullable();
            $table->string('referral_code')->unique();
            $table->string('status')->default('pending');
            $table->integer('reward_months')->default(0);
            $table->timestamps();
        });
    }
});

test('it creates a new referral code for a tenant', function () {
    $row = createTenant(['store_name' => 'Test Bakery']);
    $tenant = Tenant::find($row->id);

    $code = app(GenerateReferralCode::class)($tenant);

    expect($code)->toBeString();
    expect(Referral::query()->where('referrer_tenant_id', $tenant->id)->count())->toBe(1);
});

test('it returns existing referral code if one exists', function () {
    $row = createTenant(['store_name' => 'Test Bakery']);
    $tenant = Tenant::find($row->id);

    $code1 = app(GenerateReferralCode::class)($tenant);
    $code2 = app(GenerateReferralCode::class)($tenant);

    expect($code1)->toBe($code2);
    expect(Referral::query()->where('referrer_tenant_id', $tenant->id)->count())->toBe(1);
});
