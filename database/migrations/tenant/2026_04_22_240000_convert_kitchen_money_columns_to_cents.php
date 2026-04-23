<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Convert recipes + ingredients + ingredient_supplier money columns to bigint
 * cents. Phase 7 of N. The quantity columns (current_stock, low_stock_threshold,
 * recipe_ingredients.quantity, etc.) stay decimal — they're physical units
 * (lbs, cups, count), not money.
 */
return new class extends Migration {
    public function up(): void
    {
        DB::statement('UPDATE recipes SET cost = ROUND(cost * 100) WHERE cost IS NOT NULL');
        DB::statement('UPDATE ingredients SET cost_per_unit = ROUND(cost_per_unit * 100) WHERE cost_per_unit IS NOT NULL');
        DB::statement('UPDATE ingredient_supplier SET unit_price = ROUND(unit_price * 100) WHERE unit_price IS NOT NULL');
        DB::statement('UPDATE ingredient_supplier SET minimum_order = ROUND(minimum_order * 100) WHERE minimum_order IS NOT NULL');

        Schema::table('recipes', function (Blueprint $table): void {
            $table->bigInteger('cost')->nullable()->change();
        });

        Schema::table('ingredients', function (Blueprint $table): void {
            $table->bigInteger('cost_per_unit')->nullable()->change();
        });

        Schema::table('ingredient_supplier', function (Blueprint $table): void {
            $table->bigInteger('unit_price')->nullable()->change();
            $table->bigInteger('minimum_order')->nullable()->change();
        });
    }
};
