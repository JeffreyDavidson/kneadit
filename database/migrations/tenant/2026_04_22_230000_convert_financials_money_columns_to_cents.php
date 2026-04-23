<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Convert expenses + incomes money columns to bigint cents. Phase 5 of N.
 */
return new class extends Migration {
    public function up(): void
    {
        $expenseColumns = ['amount', 'deductible_amount'];
        $incomeColumns = ['amount'];

        foreach ($expenseColumns as $col) {
            DB::statement("UPDATE expenses SET {$col} = ROUND({$col} * 100) WHERE {$col} IS NOT NULL");
        }

        foreach ($incomeColumns as $col) {
            DB::statement("UPDATE incomes SET {$col} = ROUND({$col} * 100)");
        }

        Schema::table('expenses', function (Blueprint $table): void {
            $table->bigInteger('amount')->change();
            $table->bigInteger('deductible_amount')->nullable()->change();
        });

        Schema::table('incomes', function (Blueprint $table): void {
            $table->bigInteger('amount')->change();
        });
    }
};
