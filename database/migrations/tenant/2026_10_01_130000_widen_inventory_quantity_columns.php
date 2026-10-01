<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stock and recipe quantities keep 4 decimals so a small draw (1 g from stock held in kg,
     * 0.001) isn't rounded to zero. The wider column only adds room: existing values are unchanged.
     */
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table): void {
            $table->decimal('current_stock', 12, 4)->default(0)->change();
            $table->decimal('low_stock_threshold', 12, 4)->default(0)->change();
        });

        Schema::table('recipe_ingredients', function (Blueprint $table): void {
            $table->decimal('quantity', 12, 4)->change();
        });

        Schema::table('stock_adjustments', function (Blueprint $table): void {
            $table->decimal('quantity', 12, 4)->change();
        });
    }
};
