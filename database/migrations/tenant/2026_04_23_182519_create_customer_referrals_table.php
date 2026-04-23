<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('referred_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('reward_coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->string('status')->default('pending'); // pending | completed
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['referrer_customer_id', 'status']);
        });
    }
};
