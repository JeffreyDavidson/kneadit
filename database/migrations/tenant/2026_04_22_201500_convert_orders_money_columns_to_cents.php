<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Convert orders + order_items money columns from decimal(8,2) dollars to
 * bigint cents. First step in normalizing money storage across the schema
 * (audit found ~6 different precisions in use). The Money VO is already
 * cents-internal, so this just removes the dollar↔cent conversion at the
 * cast boundary; downstream callers see the same Money objects.
 *
 * Sequence: pre-multiply existing values by 100 (still in decimal), then
 * change the column type to bigInteger. Code deploy switches the model
 * cast from MoneyCast to MoneyCentsCast in the same release, so the
 * dollar→cent conversion never has a stale window.
 */
return new class extends Migration {
    public function up(): void
    {
        $orderColumns = ['subtotal', 'delivery_fee', 'discount_amount', 'gift_card_amount', 'total'];
        $orderItemColumns = ['unit_price'];

        foreach ($orderColumns as $col) {
            DB::statement("UPDATE orders SET {$col} = ROUND({$col} * 100)");
        }

        foreach ($orderItemColumns as $col) {
            DB::statement("UPDATE order_items SET {$col} = ROUND({$col} * 100)");
        }

        Schema::table('orders', function (Blueprint $table) use ($orderColumns) {
            foreach ($orderColumns as $col) {
                $column = $table->bigInteger($col);
                if (in_array($col, ['delivery_fee', 'discount_amount', 'gift_card_amount'], true)) {
                    $column->default(0);
                }
                $column->change();
            }
        });

        Schema::table('order_items', function (Blueprint $table) use ($orderItemColumns) {
            foreach ($orderItemColumns as $col) {
                $table->bigInteger($col)->change();
            }
        });
    }
};
