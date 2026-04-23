<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Convert coupons + coupon_transactions money columns to bigint cents.
 * Phase 3 of N (orders/order_items #310, products #312). The percentage
 * column on coupons stays decimal — it's a percentage value (0–100,
 * fractional), not money.
 */
return new class extends Migration {
    public function up(): void
    {
        $couponColumns = ['fixed_amount', 'min_order_amount'];
        $transactionColumns = ['amount'];

        foreach ($couponColumns as $col) {
            DB::statement("UPDATE coupons SET {$col} = ROUND({$col} * 100) WHERE {$col} IS NOT NULL");
        }

        foreach ($transactionColumns as $col) {
            DB::statement("UPDATE coupon_transactions SET {$col} = ROUND({$col} * 100)");
        }

        Schema::table('coupons', function (Blueprint $table): void {
            $table->bigInteger('fixed_amount')->nullable()->change();
            $table->bigInteger('min_order_amount')->nullable()->change();
        });

        Schema::table('coupon_transactions', function (Blueprint $table): void {
            $table->bigInteger('amount')->change();
        });
    }
};
