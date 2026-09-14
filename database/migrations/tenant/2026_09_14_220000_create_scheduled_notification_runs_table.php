<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scheduled_notification_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('notification_key')->unique();
            $table->timestamp('claimed_at');
            $table->timestamps();
        });
    }
};
