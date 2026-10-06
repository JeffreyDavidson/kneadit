<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SQLite does not index foreign key columns by itself. These are joined or
 * filtered on every order page, refund, cart, review and recipe lookup.
 *
 * customers.email has both a unique index and a plain index on the same single
 * column; the unique one serves every lookup, so the plain one only slows writes.
 *
 * Indexes are added in place (no table rebuild) and each is skipped if present.
 */
return new class extends Migration
{
    /** @var array<string, list<string>> */
    private const array COLUMNS = [
        'order_items' => ['order_id'],
        'refunds' => ['order_id'],
        'cart_items' => ['cart_id'],
        'reviews' => ['product_id'],
        'recipes' => ['product_id'],
        'stock_adjustments' => ['ingredient_id'],
        'customer_notes' => ['customer_id'],
        'orders' => ['coupon_id', 'gift_card_id'],
        'survey_responses' => ['order_id'],
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            foreach ($columns as $column) {
                $this->addIndex($table, $column);
            }
        }

        if (Schema::hasIndex('customers', 'customers_email_index')) {
            Schema::table('customers', function (Blueprint $table): void {
                $table->dropIndex('customers_email_index');
            });
        }
    }

    private function addIndex(string $table, string $column): void
    {
        if (Schema::hasIndex($table, [$column])) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($column): void {
            $table->index($column);
        });
    }
};
