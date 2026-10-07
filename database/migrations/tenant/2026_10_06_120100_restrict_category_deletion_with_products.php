<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deleting a category used to delete all its products (products.category_id was
 * ON DELETE CASCADE), and through them the lines of every past order. A category
 * that still has products now cannot be deleted.
 *
 * On SQLite this rebuilds products. The table has no CHECK constraint to lose and
 * the rebuild keeps its indexes (compared via sqlite_master in
 * ProtectOrderHistoryMigrationsTest). Laravel switches foreign key enforcement
 * off around the rebuild, so the rows of tables that reference products are not
 * touched; that only works outside a transaction, which is how SQLite
 * migrations run.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
        });
    }
};
