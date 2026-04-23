<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Convert loyalty_rewards.discount_amount to bigint cents. Final phase of
 * the migration started in #310. discount_percentage stays decimal — it's
 * a 0–100 fractional value, not money.
 */
return new class extends Migration {
    public function up(): void
    {
        DB::statement('UPDATE loyalty_rewards SET discount_amount = ROUND(discount_amount * 100) WHERE discount_amount IS NOT NULL');

        Schema::table('loyalty_rewards', function (Blueprint $table): void {
            $table->bigInteger('discount_amount')->nullable()->change();
        });
    }
};
