<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection('central')->create('feature_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('feature');
            $table->integer('usage_count')->default(1);
            $table->timestamp('last_used_at')->nullable();
            $table->date('date');
            $table->timestamp('created_at')->nullable();

            $table->unique(['tenant_id', 'feature', 'date']);
            $table->index('tenant_id');
            $table->index('feature');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('feature_usage_logs');
    }
};
