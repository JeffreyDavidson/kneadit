<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catering_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('event_type'); // wedding, corporate, birthday, holiday, other
            $table->date('event_date');
            $table->integer('guest_count');
            $table->decimal('budget', 10, 2)->nullable();
            $table->text('details');
            $table->text('dietary_requirements')->nullable();
            $table->text('venue_address')->nullable();
            $table->string('status')->default('inquiry'); // inquiry, quoted, confirmed, completed, cancelled
            $table->decimal('quoted_amount', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catering_inquiries');
    }
};
