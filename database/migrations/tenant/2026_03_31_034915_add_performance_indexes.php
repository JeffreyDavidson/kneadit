<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! $this->hasIndex('orders', 'orders_status_index')) {
                $table->index('status');
            }
            if (! $this->hasIndex('orders', 'orders_payment_status_index')) {
                $table->index('payment_status');
            }
            if (! $this->hasIndex('orders', 'orders_delivery_date_index')) {
                $table->index('delivery_date');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (! $this->hasIndex('products', 'products_is_active_index')) {
                $table->index('is_active');
            }
            if (! $this->hasIndex('products', 'products_is_featured_index')) {
                $table->index('is_featured');
            }
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            return (bool) DB::selectOne("SELECT 1 FROM sqlite_master WHERE type='index' AND name=?", [$index]);
        }

        $indexes = Schema::getIndexes($table);

        return collect($indexes)->contains(fn ($i) => $i['name'] === $index);
    }
};
