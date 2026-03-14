<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Rename columns to match controller expectations
            $table->renameColumn('requested_date', 'delivery_date');
            $table->renameColumn('requested_time', 'delivery_time');
            $table->renameColumn('discount', 'discount_amount');
        });

        Schema::table('orders', function (Blueprint $table) {
            // Add missing columns
            $table->string('delivery_type')->default('pickup')->after('delivery_address');
            $table->foreignId('coupon_id')->nullable()->after('discount_amount');

            // user_id should be nullable (storefront orders don't have a logged-in user)
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('delivery_date', 'requested_date');
            $table->renameColumn('delivery_time', 'requested_time');
            $table->renameColumn('discount_amount', 'discount');
            $table->dropColumn(['delivery_type', 'coupon_id']);
        });
    }
};
