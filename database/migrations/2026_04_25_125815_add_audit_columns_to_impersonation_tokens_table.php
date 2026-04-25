<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection('central')->table('impersonation_tokens', function (Blueprint $table) {
            // Audit metadata: who initiated, when consumed, from what IP.
            // Tokens are now marked consumed (consumed_at timestamp) rather
            // than deleted so the impersonation log retains full history.
            $table->unsignedBigInteger('created_by_user_id')->nullable()->after('tenant_id');
            $table->timestamp('consumed_at')->nullable()->after('expires_at');
            $table->string('consumer_ip', 45)->nullable()->after('consumed_at');

            $table->index(['created_at']);
            $table->index(['tenant_id']);
        });
    }
};
