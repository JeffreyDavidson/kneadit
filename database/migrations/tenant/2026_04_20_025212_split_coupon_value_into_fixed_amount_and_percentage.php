<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->decimal('fixed_amount', 8, 2)->nullable()->after('type');
            $table->decimal('percentage', 5, 2)->nullable()->after('fixed_amount');
        });

        DB::table('coupons')->where('type', 'fixed')->update([
            'fixed_amount' => DB::raw('value'),
        ]);

        DB::table('coupons')->where('type', 'percentage')->update([
            'percentage' => DB::raw('value'),
        ]);

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('value');
        });
    }
};
