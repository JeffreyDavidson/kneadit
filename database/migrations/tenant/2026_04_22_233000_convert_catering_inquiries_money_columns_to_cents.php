<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Convert catering_inquiries money columns to bigint cents. Phase 6 of N.
 */
return new class extends Migration {
    public function up(): void
    {
        $columns = ['budget', 'quoted_amount'];

        foreach ($columns as $col) {
            DB::statement("UPDATE catering_inquiries SET {$col} = ROUND({$col} * 100) WHERE {$col} IS NOT NULL");
        }

        Schema::table('catering_inquiries', function (Blueprint $table): void {
            $table->bigInteger('budget')->nullable()->change();
            $table->bigInteger('quoted_amount')->nullable()->change();
        });
    }
};
