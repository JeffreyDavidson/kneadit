<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('catering_inquiry_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('catering_inquiries')
                ->nullOnDelete();
        });
    }
};
