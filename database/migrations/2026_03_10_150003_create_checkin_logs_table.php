<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection('central')->create('checkin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checkin_id')->constrained('scheduled_checkins')->cascadeOnDelete();
            $table->string('tenant_id');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('checkin_logs');
    }
};
