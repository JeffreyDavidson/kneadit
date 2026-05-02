<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('webhook_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->string('event');
            $table->string('url');
            $table->json('payload');
            $table->string('signature');
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->text('response_body')->nullable()->comment('Truncated to ~2KB');
            $table->unsignedTinyInteger('attempt')->default(1);
            $table->boolean('succeeded')->default(false);
            $table->text('error')->nullable();
            $table->timestamp('dispatched_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['event', 'dispatched_at']);
            $table->index(['succeeded', 'dispatched_at']);
        });
    }
};
