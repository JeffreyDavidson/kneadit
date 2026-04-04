<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('gift_card_id')->nullable()->after('coupon_id')->constrained('gift_cards')->nullOnDelete();
            $table->decimal('gift_card_amount', 8, 2)->default(0)->after('discount_amount');
        });
    }
};
