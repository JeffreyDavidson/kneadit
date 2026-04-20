<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('loyalty_rewards', function (Blueprint $table) {
            $table->decimal('discount_amount', 8, 2)->nullable()->after('reward_type');
            $table->decimal('discount_percentage', 5, 2)->nullable()->after('discount_amount');
        });

        DB::table('loyalty_rewards')->where('reward_type', 'fixed_discount')->update([
            'discount_amount' => DB::raw('reward_value'),
        ]);

        DB::table('loyalty_rewards')->where('reward_type', 'percentage_discount')->update([
            'discount_percentage' => DB::raw('reward_value'),
        ]);

        Schema::table('loyalty_rewards', function (Blueprint $table) {
            $table->dropColumn('reward_value');
        });
    }
};
