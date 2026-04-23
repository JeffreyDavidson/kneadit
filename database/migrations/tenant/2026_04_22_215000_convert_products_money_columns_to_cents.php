<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Convert products money columns from decimal(8,2) dollars to bigint cents.
 * Phase 2 of N (orders + order_items shipped in v1.10.20). Same pattern:
 * pre-multiply existing values, change the column type, model cast switches
 * to MoneyCentsCast in the same release.
 */
return new class extends Migration {
    public function up(): void
    {
        $columns = ['price', 'cost'];

        foreach ($columns as $col) {
            DB::statement("UPDATE products SET {$col} = ROUND({$col} * 100) WHERE {$col} IS NOT NULL");
        }

        Schema::table('products', function (Blueprint $table): void {
            $table->bigInteger('price')->change();
            $table->bigInteger('cost')->nullable()->change();
        });
    }
};
