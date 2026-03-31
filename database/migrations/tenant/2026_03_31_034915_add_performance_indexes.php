<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('payment_status');
            $table->index('delivery_date');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('is_featured');
        });
    }
};
