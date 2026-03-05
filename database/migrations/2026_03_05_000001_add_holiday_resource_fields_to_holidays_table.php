<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->date('order_deadline')->nullable()->after('date');
            $table->date('prep_start')->nullable()->after('order_deadline');
            $table->integer('max_orders')->nullable()->after('prep_start');
            $table->boolean('is_active')->default(true)->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropColumn(['order_deadline', 'prep_start', 'max_orders', 'is_active']);
        });
    }
};
