<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_waitlists', function (Blueprint $table) {
            $table->index('product_id');
        });

        Schema::table('ingredient_supplier', function (Blueprint $table) {
            $table->index('supplier_id');
        });

        Schema::table('recipe_ingredients', function (Blueprint $table) {
            $table->index('recipe_id');
            $table->index('ingredient_id');
        });

        Schema::table('survey_responses', function (Blueprint $table) {
            $table->index('survey_id');
        });

        Schema::table('gift_card_transactions', function (Blueprint $table) {
            $table->index('gift_card_id');
        });

        Schema::table('loyalty_points', function (Blueprint $table) {
            $table->index('customer_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('product_waitlists', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });

        Schema::table('ingredient_supplier', function (Blueprint $table) {
            $table->dropIndex(['supplier_id']);
        });

        Schema::table('recipe_ingredients', function (Blueprint $table) {
            $table->dropIndex(['recipe_id']);
            $table->dropIndex(['ingredient_id']);
        });

        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropIndex(['survey_id']);
        });

        Schema::table('gift_card_transactions', function (Blueprint $table) {
            $table->dropIndex(['gift_card_id']);
        });

        Schema::table('loyalty_points', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });
    }
};
