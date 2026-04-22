<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->string('payment_method')->default('cash');
            $table->decimal('subtotal', 8, 2);
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->decimal('discount_amount', 8, 2)->default(0);
            $table->decimal('total', 8, 2);
            $table->string('paypal_invoice_id')->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('delivery_type')->default('pickup');
            $table->date('delivery_date')->nullable();
            $table->time('delivery_time')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('coupon_id')->nullable();
            $table->timestamp('review_request_sent_at')->nullable();
            $table->string('stripe_checkout_session_id')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->timestamps();
        });
    }
};
