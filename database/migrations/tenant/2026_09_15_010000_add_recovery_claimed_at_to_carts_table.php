<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table): void {
            $table->timestamp('recovery_claimed_at')->nullable()->after('recovery_sent_at');
            $table->index(['last_activity_at', 'recovery_sent_at', 'recovery_claimed_at']);
        });
    }
};
