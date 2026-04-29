<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contact_message_replies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contact_message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->comment('Staff member who sent the reply');
            $table->string('subject');
            $table->text('body');
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['contact_message_id', 'sent_at']);
        });
    }
};
