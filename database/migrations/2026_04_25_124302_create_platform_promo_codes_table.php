<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection('central')->create('platform_promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('coupon_id');
            $table->string('promotion_code_id');
            $table->unsignedTinyInteger('percent_off')->nullable();
            $table->unsignedInteger('amount_off_cents')->nullable();
            $table->string('duration');
            $table->unsignedTinyInteger('duration_in_months')->nullable();
            $table->unsignedInteger('max_redemptions')->default(1);
            $table->timestamp('expires_at')->nullable();
            $table->string('tenant_id')->nullable();
            $table->string('name')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['created_at']);
            $table->index(['tenant_id']);
        });
    }
};
