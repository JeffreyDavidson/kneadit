<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->comment('Staff member who initiated the refund');
            $table->unsignedBigInteger('amount')->comment('cents');
            $table->string('reason')->nullable();
            $table->string('stripe_refund_id')->nullable();
            $table->timestamps();

            $table->index('stripe_refund_id');
        });
    }
};
