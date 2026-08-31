<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('customer_notes', function (Blueprint $table): void {
            $table->dropForeign(['created_by']);
            $table->foreignId('created_by')->nullable()->change();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('staff_invitations', function (Blueprint $table): void {
            $table->dropForeign(['invited_by']);
            $table->foreignId('invited_by')->nullable()->change();
            $table->foreign('invited_by')->references('id')->on('users')->nullOnDelete();
        });
    }
};
