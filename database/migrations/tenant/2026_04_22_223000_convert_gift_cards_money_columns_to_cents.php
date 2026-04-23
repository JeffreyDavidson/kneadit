<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Convert gift_cards + gift_card_transactions money columns to bigint cents.
 * Phase 4 of N. orders.gift_card_amount was already migrated in #310 (it's
 * an orders column).
 */
return new class extends Migration {
    public function up(): void
    {
        $cardColumns = ['initial_balance', 'current_balance'];
        $transactionColumns = ['amount'];

        foreach ($cardColumns as $col) {
            DB::statement("UPDATE gift_cards SET {$col} = ROUND({$col} * 100)");
        }

        foreach ($transactionColumns as $col) {
            DB::statement("UPDATE gift_card_transactions SET {$col} = ROUND({$col} * 100)");
        }

        Schema::table('gift_cards', function (Blueprint $table): void {
            $table->bigInteger('initial_balance')->change();
            $table->bigInteger('current_balance')->change();
        });

        Schema::table('gift_card_transactions', function (Blueprint $table): void {
            $table->bigInteger('amount')->change();
        });
    }
};
