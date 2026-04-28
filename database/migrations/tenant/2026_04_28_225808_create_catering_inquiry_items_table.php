<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('catering_inquiry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catering_inquiry_id')
                ->constrained('catering_inquiries')
                ->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->text('special_instructions')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['catering_inquiry_id', 'sort_order']);
        });
    }
};
