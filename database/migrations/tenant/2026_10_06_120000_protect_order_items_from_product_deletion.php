<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Deleting a product used to delete its line from every past order
 * (order_items.product_id was ON DELETE CASCADE). The line now stays with a
 * null product, keeping its quantity, price and name.
 *
 * Order lines created before names were copied onto them have no name, so the
 * product's current name is copied first; a line whose product row is later
 * removed still says what was sold.
 *
 * On SQLite this rebuilds order_items. The table has no CHECK constraint to
 * lose, nothing else references it, and the rebuild keeps its indexes
 * (compared via sqlite_master in ProtectOrderHistoryMigrationsTest).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'UPDATE order_items SET name = (SELECT products.name FROM products WHERE products.id = order_items.product_id) '
            .'WHERE name IS NULL AND product_id IS NOT NULL',
        );

        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });
    }
};
