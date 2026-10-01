<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->bigInteger('original_subtotal')->nullable()->after('subtotal');
            $table->bigInteger('original_discount_amount')->nullable()->after('discount_amount');
        });
    }
};
