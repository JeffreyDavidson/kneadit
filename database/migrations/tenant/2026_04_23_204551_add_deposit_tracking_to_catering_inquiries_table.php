<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('catering_inquiries', function (Blueprint $table) {
            $table->bigInteger('deposit_amount')->nullable()->after('quoted_amount');
            $table->timestamp('deposit_paid_at')->nullable()->after('deposit_amount');
            $table->string('deposit_reference')->nullable()->after('deposit_paid_at');
        });
    }
};
