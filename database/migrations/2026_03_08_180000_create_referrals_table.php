<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->string('referrer_tenant_id');
            $table->string('referred_tenant_id')->nullable();
            $table->string('referral_code')->unique();
            $table->string('referred_email')->nullable();
            $table->string('status')->default('pending'); // pending, completed, rewarded
            $table->integer('reward_months')->default(1);
            $table->timestamps();

            $table->foreign('referrer_tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('referred_tenant_id')->references('id')->on('tenants')->onDelete('set null');
        });
    }
};
