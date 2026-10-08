<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records when the bakery was alerted about a low rating, so a review edited up
 * and back down again alerts only once. A plain nullable column is added in
 * place on SQLite; the table (and its rating CHECK) is not rebuilt.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('reviews', 'low_rating_alerted_at')) {
            return;
        }

        Schema::table('reviews', function (Blueprint $table): void {
            $table->timestamp('low_rating_alerted_at')->nullable();
        });
    }
};
