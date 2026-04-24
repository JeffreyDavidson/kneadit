<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            // Platform-admin-granted "comp account" flag. When true, the tenant
            // bypasses billing entirely (no Stripe subscription required, no
            // trial expiry, no plan checks blocking access) and SubscriptionTier
            // ::resolve() returns the highest tier so every Pro feature is
            // unlocked. Tenant settings still control which features are
            // surfaced — free-forever just unblocks them at the billing layer.
            $table->boolean('free_forever')->default(false)->after('plan');
        });
    }
};
