<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Set when the cart was saved from a signed-in customer session. A plain
     * indexed column rather than a foreign key: on SQLite a foreign key on an
     * existing table rebuilds it, and erasing a customer deletes their carts
     * explicitly anyway. Adding a nullable column and an index does not rebuild
     * the table.
     */
    public function up(): void
    {
        if (Schema::hasColumn('carts', 'customer_id')) {
            return;
        }

        Schema::table('carts', function (Blueprint $table): void {
            $table->unsignedBigInteger('customer_id')->nullable()->after('cart_token');
            $table->index('customer_id');
        });
    }
};
