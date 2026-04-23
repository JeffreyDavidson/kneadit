<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customer_campaigns', function (Blueprint $table) {
            $table->timestamp('scheduled_at')->nullable()->after('status');
            $table->index(['status', 'scheduled_at']);
        });
    }
};
