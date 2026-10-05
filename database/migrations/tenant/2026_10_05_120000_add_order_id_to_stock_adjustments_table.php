<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tie each usage and restock row to the order that caused it, so a cancelled order can put back
     * exactly what it took. The column is added with a plain ALTER TABLE because the schema builder
     * rebuilds the whole table on SQLite when it adds a foreign key to an existing one.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('stock_adjustments', 'order_id')) {
            DB::statement('ALTER TABLE stock_adjustments ADD COLUMN order_id INTEGER NULL REFERENCES orders (id) ON DELETE SET NULL');

            Schema::table('stock_adjustments', function (Blueprint $table): void {
                $table->index('order_id');
            });
        }

        $this->linkExistingRows();
    }

    /**
     * Rows written before this column existed only name their order in the notes
     * ("Order #1001" for usage, "Order #1001 cancelled" for a restock).
     */
    private function linkExistingRows(): void
    {
        $orderIds = DB::table('orders')->pluck('id', 'order_number');

        DB::table('stock_adjustments')
            ->whereNull('order_id')
            ->whereIn('type', ['usage', 'restock'])
            ->where('notes', 'like', 'Order #%')
            ->orderBy('id')
            ->each(function (object $row) use ($orderIds): void {
                $orderNumber = str(Arr::string(get_object_vars($row), 'notes'))->after('Order #')->before(' cancelled')->toString();

                if (! $orderIds->has($orderNumber)) {
                    return;
                }

                DB::table('stock_adjustments')->where('id', $row->id)->update(['order_id' => $orderIds->get($orderNumber)]);
            });
    }
};
