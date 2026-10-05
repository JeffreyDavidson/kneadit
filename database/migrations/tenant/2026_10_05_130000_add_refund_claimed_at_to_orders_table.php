<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // A plain nullable column, which SQLite adds in place: the orders table is
    // not rebuilt, so its constraints and indexes are untouched.
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->timestamp('refund_claimed_at')->nullable();
        });
    }
};
