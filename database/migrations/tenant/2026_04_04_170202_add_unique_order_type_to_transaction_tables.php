<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('coupon_transactions', function (Blueprint $table) {
            $table->unique(['order_id', 'type']);
        });

        Schema::table('gift_card_transactions', function (Blueprint $table) {
            $table->unique(['order_id', 'type']);
        });
    }
};
