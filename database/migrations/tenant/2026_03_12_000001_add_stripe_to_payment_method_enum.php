<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support ALTER COLUMN for enums, so recreate
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method_new')->default('cash')->after('payment_method');
            $table->string('stripe_checkout_session_id')->nullable()->after('payment_method');
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_checkout_session_id');
        });

        // Copy data
        \Illuminate\Support\Facades\DB::table('orders')->update([
            'payment_method_new' => \Illuminate\Support\Facades\DB::raw('payment_method'),
        ]);

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('payment_method_new', 'payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['stripe_checkout_session_id', 'stripe_payment_intent_id']);
        });
    }
};
